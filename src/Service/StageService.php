<?php

namespace App\Service;

use App\Exception\ValidationException;

class StageService
{
    public function findAll(): array
    {
        // buscaria do banco
        return [];
    }

    public function create(array $data): array
    {
        $errors = [];

        if (empty($data['name']) || !is_string($data['name'])) {
            $errors['name'] = 'O nome é obrigatório.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // salvaria no banco
        return $data;
    }

    public function update(int $_id, array $data): array
    {
        $errors = [];

        if (array_key_exists('name', $data) && empty($data['name'])) {
            $errors['name'] = 'O nome não pode ser vazio.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // atualizaria no banco
        return $data;
    }
}
