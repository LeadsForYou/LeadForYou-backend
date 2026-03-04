<?php

namespace App\Service;

use App\Exception\ValidationException;

class UserService
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

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Um e-mail válido é obrigatório.';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'A senha deve ter no mínimo 8 caracteres.';
        }

        if (empty($data['role'])) {
            $errors['role'] = 'O perfil é obrigatório.';
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

        if (array_key_exists('email', $data) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }

        if (array_key_exists('password', $data) && strlen($data['password']) < 8) {
            $errors['password'] = 'A senha deve ter no mínimo 8 caracteres.';
        }

        if (array_key_exists('role', $data) && empty($data['role'])) {
            $errors['role'] = 'O perfil não pode ser vazio.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // atualizaria no banco
        return $data;
    }
}
