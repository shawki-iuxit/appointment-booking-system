<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    protected function successResponse(
        $data = null, 
        string $message = 'Operation successful', 
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }

    protected function errorResponse(
        string $message = 'Operation failed', 
        $errors = null, 
        int $statusCode = 500, 
        string $errorCode = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        return response()->json($response, $statusCode);
    }

    protected function validationErrorResponse($errors): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            $errors,
            422,
            'VALIDATION_ERROR'
        );
    }

    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse(
            $message,
            null,
            404,
            'RESOURCE_NOT_FOUND'
        );
    }

    protected function unauthorizedResponse(string $message = 'Unauthorized access'): JsonResponse
    {
        return $this->errorResponse(
            $message,
            null,
            401,
            'UNAUTHORIZED'
        );
    }

    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(string $message = 'Operation completed successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], 204);
    }
}