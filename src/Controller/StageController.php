<?php

namespace App\Controller;

use App\Service\StageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class StageController extends AbstractController
{
    public function __construct(private readonly StageService $stageService) {}

    public function list(): JsonResponse
    {
        return $this->json($this->stageService->findAll());
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();

        return $this->json($this->stageService->create($data), JsonResponse::HTTP_CREATED);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();

        return $this->json($this->stageService->update($id, $data));
    }
}
