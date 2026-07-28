<?php

namespace App\Services\Sync;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class SyncTableHandler
{
    protected string $table;

    protected array $definition;

    protected string $modelClass;

    public function __construct(string $table, array $definition)
    {
        $this->table = $table;
        $this->definition = $definition;
        $this->modelClass = (string) ($definition['model'] ?? '');

        if (!$this->modelClass || !class_exists($this->modelClass)) {
            throw new InvalidArgumentException('Invalid sync model for table ' . $table);
        }
    }

    public function isAllowedForRole(?string $role): bool
    {
        $allowedRoles = $this->definition['allowed_roles'] ?? [];

        if (empty($allowedRoles)) {
            return true;
        }

        return in_array($role, $allowedRoles, true);
    }

    protected function newModel(): Model
    {
        $class = $this->modelClass;

        return new $class();
    }

    protected function getFillableFields(Model $model): array
    {
        $fillable = $model->getFillable();

        if (!empty($fillable)) {
            return $fillable;
        }

        return array_values((array) ($this->definition['fields'] ?? []));
    }

    protected function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }

    protected function mapValue($value, array $context)
    {
        if (is_array($value)) {
            return array_map(function ($item) use ($context) {
                return $this->mapValue($item, $context);
            }, $value);
        }

        if (is_string($value) && $this->isUuid($value) && isset($context['local_to_server'][$value])) {
            return $context['local_to_server'][$value];
        }

        return $value;
    }

    protected function normalizePayload(array $record, Model $model, array $context): array
    {
        $payload = $record['data'] ?? $record['payload'] ?? $record['formData'] ?? [];

        if (!is_array($payload)) {
            $payload = [];
        }

        $payload = array_map(function ($value) use ($context) {
            return $this->mapValue($value, $context);
        }, $payload);

        $payload = Arr::except($payload, [
            'id',
            'local_id',
            'localId',
            'server_id',
            'serverId',
            'synced',
            'sync_status',
            'syncStatus',
            'local_action',
            'localAction',
            'sync_error',
            'syncError',
            'last_error',
            'lastError',
            'queue_state',
            'queueState',
            'request_uuid',
            'requestUuid',
            'request_key',
            'requestKey',
            'table',
            'table_name',
            'tableName',
            'operation',
            'action',
            'timestamp',
            'retry_count',
            'retryCount',
            'meta',
        ]);

        $fillable = $this->getFillableFields($model);

        if (!empty($fillable)) {
            $payload = Arr::only($payload, $fillable);
        }

        $canApplyDepId = empty($fillable) || in_array('depID', $fillable, true) || array_key_exists('depID', $payload);

        if ($canApplyDepId && empty($payload['depID']) && isset($context['user'])) {
            $payload['depID'] = $context['user']->depID ?? null;
        }

        $userIdFields = ['seller', 'created_by', 'user', 'received_by', 'purchased'];

        foreach ($userIdFields as $field) {
            $canApplyDefault = empty($fillable) || in_array($field, $fillable, true) || array_key_exists($field, $payload);

            if ($canApplyDefault && (!array_key_exists($field, $payload) || empty($payload[$field]))) {
                if (isset($context['user']) && $context['user']) {
                    $payload[$field] = $context['user']->id;
                }
            }
        }

        return $payload;
    }

    protected function resolveServerId(array $record, array $context): ?int
    {
        $serverId = $record['server_id'] ?? $record['data']['server_id'] ?? $record['data']['id'] ?? null;

        if (is_numeric($serverId) && (int) $serverId > 0) {
            return (int) $serverId;
        }

        $localId = $record['local_id'] ?? $record['record_local_id'] ?? $record['recordLocalId'] ?? null;

        if ($localId && isset($context['local_to_server'][$localId])) {
            return (int) $context['local_to_server'][$localId];
        }

        return null;
    }

    protected function buildResult(array $record, string $status, ?int $serverId = null, ?string $message = null): array
    {
        return [
            'table' => $this->table,
            'operation' => $record['operation'] ?? $record['action'] ?? 'create',
            'local_id' => $record['local_id'] ?? $record['record_local_id'] ?? null,
            'request_uuid' => $record['request_uuid'] ?? $record['uuid'] ?? null,
            'server_id' => $serverId,
            'status' => $status,
            'message' => $message,
        ];
    }

    public function process(array $record, array &$context): array
    {
        $operation = strtolower((string) ($record['operation'] ?? $record['action'] ?? 'create'));

        try {
            return DB::transaction(function () use ($record, $context, $operation) {
                $model = $this->newModel();
                $payload = $this->normalizePayload($record, $model, $context);
                $localId = $record['local_id'] ?? $record['record_local_id'] ?? $record['recordLocalId'] ?? null;

                if ($operation === 'create') {
                    return $this->processCreate($model, $payload, $context, $localId, $record);
                }

                if ($operation === 'update') {
                    return $this->processUpdate($model, $payload, $context, $localId, $record);
                }

                if ($operation === 'delete') {
                    return $this->processDelete($model, $payload, $context, $localId, $record);
                }

                return $this->buildResult($record, 'failed', null, 'Unsupported operation.');
            });
        } catch (Throwable $throwable) {
            return $this->buildResult($record, 'failed', null, $throwable->getMessage());
        }
    }

    protected function processCreate(Model $model, array $payload, array &$context, $localId, array $record): array
    {
        $model->fill($payload);
        $model->save();

        $serverId = (int) $model->getKey();

        if ($localId) {
            $context['local_to_server'][$localId] = $serverId;
        }

        return $this->buildResult($record, 'synced', $serverId);
    }

    protected function processUpdate(Model $model, array $payload, array &$context, $localId, array $record): array
    {
        $serverId = $this->resolveServerId($record, $context);

        if (!$serverId) {
            throw new InvalidArgumentException('Unable to resolve server id for update.');
        }

        $existing = $model->newQuery()->find($serverId);

        if (!$existing) {
            throw new InvalidArgumentException('Record not found for update.');
        }

        $existing->fill($payload);
        $existing->save();

        if ($localId) {
            $context['local_to_server'][$localId] = (int) $existing->getKey();
        }

        return $this->buildResult($record, 'synced', (int) $existing->getKey());
    }

    protected function processDelete(Model $model, array $payload, array &$context, $localId, array $record): array
    {
        $serverId = $this->resolveServerId($record, $context);

        if (!$serverId) {
            throw new InvalidArgumentException('Unable to resolve server id for delete.');
        }

        $existing = $model->newQuery()->find($serverId);

        if (!$existing) {
            return $this->buildResult($record, 'synced', $serverId, 'Record already removed.');
        }

        $existing->delete();

        if ($localId) {
            $context['local_to_server'][$localId] = $serverId;
        }

        return $this->buildResult($record, 'synced', $serverId);
    }
}
