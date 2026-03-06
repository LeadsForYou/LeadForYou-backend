<?php

namespace App\Controller;

use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use App\Http\ApiResponse;
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
            return ApiResponse::notFound('Nenhum lead encontrado.');
        }

        return ApiResponse::success($data);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->getContent() !== '' ? $request->toArray() : [];

        try {
            return ApiResponse::created($this->leadService->create($data));
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors());
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->getContent() !== '' ? $request->toArray() : [];

        try {
            return ApiResponse::success($this->leadService->update($id, $data));
        } catch (EntityNotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors());
        }
    }
}
