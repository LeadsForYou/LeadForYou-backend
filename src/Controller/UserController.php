<?php

namespace App\Controller;

use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use App\Http\ApiResponse;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserController extends AbstractController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function list(): JsonResponse
    {
        $data = $this->userService->findAll();

        if (empty($data)) {
            return ApiResponse::notFound('Nenhum usuário encontrado.');
        }

        return ApiResponse::success($data);
    }

    public function create(Request $request): JsonResponse
    {
        $data = '' !== $request->getContent() ? $request->toArray() : [];

        try {
            return ApiResponse::created($this->userService->create($data));
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors());
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = '' !== $request->getContent() ? $request->toArray() : [];

        try {
            return ApiResponse::success($this->userService->update($id, $data));
        } catch (EntityNotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors());
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $this->userService->delete($id);

            return ApiResponse::deleted();
        } catch (EntityNotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}
