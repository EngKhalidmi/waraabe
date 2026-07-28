<?php

namespace App\Http\Controllers;

use App\Services\Sync\SyncProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function store(Request $request, SyncProcessor $processor): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.table' => ['required', 'string'],
            'records.*.operation' => ['required', 'in:create,update,delete'],
            'records.*.local_id' => ['required', 'string'],
            'records.*.data' => ['nullable', 'array'],
            'records.*.request_uuid' => ['nullable', 'string'],
            'records.*.request_key' => ['nullable', 'string'],
            'records.*.server_id' => ['nullable'],
        ]);

        if (isset($validated['user_id']) && (int) $validated['user_id'] !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'The authenticated user does not match the sync payload user.',
            ], 403);
        }

        $result = $processor->processBatch($validated['records'], $user);

        return response()->json([
            'success' => true,
            'results' => $result['results'],
            'summary' => $result['summary'],
        ]);
    }
}
