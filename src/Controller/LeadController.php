<?php

namespace App\Controller;

use App\Exception\ValidationException;
use App\Service\LeadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class LeadController extends AbstractController
{
    public function __construct(private readonly LeadService $leadService) {}

    public function list(): JsonResponse
    {
        $data = $this->leadService->findAll();

        if (empty($data)) {
            return $this->json(['message' => 'Nenhum lead encontrado.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($data);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->getContent() !== '' ? $request->toArray() : [];

        try {
            return $this->json($this->leadService->create($data), JsonResponse::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->getContent() !== '' ? $request->toArray() : [];

        try {
            return $this->json($this->leadService->update($id, $data));
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
