<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait Response
{
    public function responseMessage(string $module, string $action)
    {
        $messages = [
            'index' => ' list getting successfully!',
            'show' => ' showing successfully!',
            'store' => ' stored successfully!',
            'update' => ' updated successfully!',
            'destroy' => ' deleted successfully!',
            'validation' => "'s validation response!",



            'authentication' => " Attempted request are Authenticated",
            'unauthentication' => " Attempted request are Unauthenticated",
            'unauthenticated-permission' => " Attempted request are Unauthenticated Permission",
        ];
        return preg_replace('/(?<!^)(?=[A-Z])/', ' ', $module) . ($messages[$action] ?? 'Response value showing');
    }

    public function successResponse(string $message, $data, int $httpResponseCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => $httpResponseCode,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $httpResponseCode);
    }

    public function errorResponse(string $message, ?array $errors = [], int $httpResponseCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => $httpResponseCode,
            'message' => $message ?? null,
            'data' => null,
            'errors' => $errors,
        ], $httpResponseCode);
    }
}
