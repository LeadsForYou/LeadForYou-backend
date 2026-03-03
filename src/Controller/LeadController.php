<?php

namespace App\Controller;

use App\Service\LeadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class LeadController extends AbstractController
{
    public function __construct(private readonly LeadService $leadService) {}

    public function list(): JsonResponse
    {
        return $this->json($this->leadService->findAll());
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();

        return $this->json($this->leadService->create($data), JsonResponse::HTTP_CREATED);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();

        return $this->json($this->leadService->update($id, $data));
    }
}
