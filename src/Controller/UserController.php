<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserController extends AbstractController
{
    public function __construct(private readonly UserService $userService) {}

    public function list(): JsonResponse
    {
        return $this->json($this->userService->findAll());
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();

        return $this->json($this->userService->create($data), JsonResponse::HTTP_CREATED);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();

        return $this->json($this->userService->update($id, $data));
    }
}
