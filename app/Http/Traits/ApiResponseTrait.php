<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Standard success JSON response.
     */
    protected function success(mixed $data = null, string $message = 'Operation successful.', int $statusCode = 200, array $extra = []): JsonResponse
    {
        $payload = array_merge([
            'status' => true,
            'message' => $message,
        ], $extra);

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $statusCode);
    }

    /**
     * Standard error JSON response.
     */
    protected function error(string $message = 'An error occurred.', int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'status' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }
}
