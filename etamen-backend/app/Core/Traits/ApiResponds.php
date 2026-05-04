<?php

namespace App\Core\Traits;

use App\Core\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

trait ApiResponds
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $status);
    }

    protected function error(string $message, array $errors = [], int $status = 400, mixed $data = null): JsonResponse
    {
        return ApiResponse::error($message, $errors, $status, $data);
    }
}
