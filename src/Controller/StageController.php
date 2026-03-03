<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


final class StageController extends AbstractController
{

    public function list(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/getStaged.json');
        // pegaria do banco

        return $this->json(json_decode($dados));
    }

    public function create(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/postStage.json');
        // salvaria no banco

        return $this->json(json_decode($dados), JsonResponse::HTTP_CREATED);
    }

    public function update(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/putStage.json');
        // atualizaria no banco

        return $this->json(json_decode($dados));
    }
}
