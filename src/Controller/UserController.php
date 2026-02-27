<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'user_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/getusers.json');
        // pegaria do banco

        return $this->json(json_decode($dados));
    }

    #[Route('/user', name: 'user_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/postuser.json');
        // salvaria no banco

        return $this->json(json_decode($dados), JsonResponse::HTTP_CREATED);
    }

    #[Route('/user/{id}', name: 'user_update', methods: ['PUT'])]
    public function update(): JsonResponse
    {
        $dados = file_get_contents(__DIR__ . '/mock/putUser.json');
        // atualizaria no banco

        return $this->json(json_decode($dados));
    }
}
