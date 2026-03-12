<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public static function success(array $data, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        return new JsonResponse(['data' => $data], $status);
    }

    public static function created(array $data): JsonResponse
    {
        return new JsonResponse(['data' => $data], JsonResponse::HTTP_CREATED);
    }

    public static function notFound(string $message): JsonResponse
    {
        return new JsonResponse(['message' => $message], JsonResponse::HTTP_NOT_FOUND);
    }

    public static function validationError(array $errors): JsonResponse
    {
        return new JsonResponse(
            ['message' => 'Dados inválidos.', 'errors' => $errors],
            JsonResponse::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
