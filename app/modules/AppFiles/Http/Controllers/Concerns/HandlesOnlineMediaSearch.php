<?php

namespace Modules\AppFiles\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\AppFiles\Support\OnlineMediaSearchService;
use RuntimeException;

trait HandlesOnlineMediaSearch
{
    protected function performOnlineMediaSearch(Request $request, OnlineMediaSearchService $search, string $type = 'all'): JsonResponse
    {
        $validated = $request->validate([
            'keyword' => ['required', 'string', 'max:255'],
            'source' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $results = $search->search($validated['keyword'], $validated['source'], (string) ($validated['type'] ?? $type));
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'keyword' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => __('Media search completed successfully.'),
            'data' => $results,
        ]);
    }
}
