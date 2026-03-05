<?php

namespace App\Controller;

use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use App\Service\StageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class StageController extends AbstractController
{
    public function __construct(private readonly StageService $stageService) {}

    public function list(): JsonResponse
    {
        $data = $this->stageService->findAll();

        if (empty($data)) {
            return $this->json(['message' => 'Nenhum estágio encontrado.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($data);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->getContent() !== '' ? $request->toArray() : [];

        try {
            return $this->json($this->stageService->create($data), JsonResponse::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->getContent() !== '' ? $request->toArray() : [];

        try {
            return $this->json($this->stageService->update($id, $data));
        } catch (EntityNotFoundException $e) {
            return $this->json(['message' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
