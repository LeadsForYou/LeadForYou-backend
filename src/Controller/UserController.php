<?php

namespace App\Controller;

use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserController extends AbstractController
{
    public function __construct(private readonly UserService $userService) {}

    public function list(): JsonResponse
    {
        $data = $this->userService->findAll();

        if (empty($data)) {
            return $this->json(['message' => 'Nenhum usuário encontrado.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($data);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->getContent() !== '' ? $request->toArray() : [];

        try {
            return $this->json($this->userService->create($data), JsonResponse::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->getContent() !== '' ? $request->toArray() : [];

        try {
            return $this->json($this->userService->update($id, $data));
        } catch (EntityNotFoundException $e) {
            return $this->json(['message' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
