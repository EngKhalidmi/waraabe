<?php

namespace App\Services\Sync;

use Illuminate\Support\Arr;
use Throwable;

class SyncProcessor
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('sync.tables', []);
    }

    protected function getDefinition(string $table): ?array
    {
        return $this->config[$table] ?? null;
    }

    protected function getPriority(string $table): int
    {
        $definition = $this->getDefinition($table);

        return (int) ($definition['priority'] ?? 999);
    }

    protected function getDependencyDepth(string $table, array &$memo = [], array &$stack = []): int
    {
        if (isset($memo[$table])) {
            return (int) $memo[$table];
        }

        if (isset($stack[$table])) {
            return 0;
        }

        $stack[$table] = true;
        $definition = $this->getDefinition($table);
        $dependencies = $definition['depends_on'] ?? [];
        $depth = 0;

        foreach ($dependencies as $dependency) {
            $depth = max($depth, $this->getDependencyDepth($dependency, $memo, $stack) + 1);
        }

        $memo[$table] = $depth;
        unset($stack[$table]);

        return $depth;
    }

    protected function getAuthorizedRole(string $role): bool
    {
        return true;
    }

    protected function createHandler(string $table): SyncTableHandler
    {
        $definition = $this->getDefinition($table);

        if (!$definition) {
            throw new \InvalidArgumentException('Table "' . $table . '" is not registered for synchronization.');
        }

        return new SyncTableHandler($table, $definition);
    }

    protected function sortRecords(array $records): array
    {
        $memo = [];

        usort($records, function ($left, $right) use (&$memo) {
            $leftTable = strtolower((string) ($left['table'] ?? $left['table_name'] ?? $left['tableName'] ?? ''));
            $rightTable = strtolower((string) ($right['table'] ?? $right['table_name'] ?? $right['tableName'] ?? ''));
            $leftPriority = $this->getPriority($leftTable);
            $rightPriority = $this->getPriority($rightTable);

            if ($leftPriority !== $rightPriority) {
                return $leftPriority <=> $rightPriority;
            }

            $leftDepth = $this->getDependencyDepth($leftTable, $memo);
            $rightDepth = $this->getDependencyDepth($rightTable, $memo);

            if ($leftDepth !== $rightDepth) {
                return $leftDepth <=> $rightDepth;
            }

            $leftTimestamp = (string) ($left['timestamp'] ?? $left['created_at'] ?? $left['updated_at'] ?? '');
            $rightTimestamp = (string) ($right['timestamp'] ?? $right['created_at'] ?? $right['updated_at'] ?? '');

            if ($leftTimestamp !== $rightTimestamp) {
                return $leftTimestamp <=> $rightTimestamp;
            }

            return strcmp((string) ($left['request_uuid'] ?? $left['uuid'] ?? ''), (string) ($right['request_uuid'] ?? $right['uuid'] ?? ''));
        });

        return $records;
    }

    public function processBatch(array $records, $user): array
    {
        $orderedRecords = $this->sortRecords($records);
        $context = [
            'user' => $user,
            'local_to_server' => [],
        ];
        $results = [];

        foreach ($orderedRecords as $record) {
            $table = strtolower((string) ($record['table'] ?? $record['table_name'] ?? $record['tableName'] ?? ''));
            $definition = $this->getDefinition($table);

            if (!$definition) {
                $results[] = [
                    'table' => $table,
                    'operation' => $record['operation'] ?? 'create',
                    'local_id' => $record['local_id'] ?? null,
                    'request_uuid' => $record['request_uuid'] ?? $record['uuid'] ?? null,
                    'server_id' => null,
                    'status' => 'failed',
                    'message' => 'Table is not registered for synchronization.',
                ];
                continue;
            }

            $handler = $this->createHandler($table);

            if (!$handler->isAllowedForRole($user->role ?? null)) {
                $results[] = [
                    'table' => $table,
                    'operation' => $record['operation'] ?? 'create',
                    'local_id' => $record['local_id'] ?? null,
                    'request_uuid' => $record['request_uuid'] ?? $record['uuid'] ?? null,
                    'server_id' => null,
                    'status' => 'failed',
                    'message' => 'You are not authorized to sync this table.',
                ];
                continue;
            }

            $result = $handler->process($record, $context);
            $results[] = $result;

            if (($result['status'] ?? null) === 'synced' && !empty($result['local_id']) && !empty($result['server_id'])) {
                $context['local_to_server'][$result['local_id']] = (int) $result['server_id'];
            }
        }

        return [
            'results' => $results,
            'summary' => [
                'total' => count($results),
                'synced' => count(array_filter($results, function ($result) {
                    return ($result['status'] ?? null) === 'synced';
                })),
                'failed' => count(array_filter($results, function ($result) {
                    return ($result['status'] ?? null) !== 'synced';
                })),
            ],
        ];
    }
}
