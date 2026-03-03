<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


final class LeadController extends AbstractController
{

    public function list(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/getLead.json');
        // pegaria do banco

        return $this->json(json_decode($dados));
    }

    public function create(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/postLead.json');
        // salvaria no banco

        return $this->json(json_decode($dados), JsonResponse::HTTP_CREATED);
    }

    public function update(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/putLead.json');
        // atualizaria no banco

        return $this->json(json_decode($dados));
    }
}
