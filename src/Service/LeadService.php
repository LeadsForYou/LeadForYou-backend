<?php

namespace App\Service;

use App\Exception\ValidationException;

class LeadService
{
    public function findAll(): array
    {
        // buscaria do banco
        return [];
    }

    public function create(array $data): array
    {
        $errors = [];

        if (empty($data['userId']) || !is_int($data['userId']) || $data['userId'] <= 0) {
            $errors['userId'] = 'O userId é obrigatório e deve ser um inteiro positivo.';
        }

        if (empty($data['stageId']) || !is_int($data['stageId']) || $data['stageId'] <= 0) {
            $errors['stageId'] = 'O stageId é obrigatório e deve ser um inteiro positivo.';
        }

        if (empty($data['name']) || !is_string($data['name'])) {
            $errors['name'] = 'O nome é obrigatório.';
        }

        if (empty($data['company']) || !is_string($data['company'])) {
            $errors['company'] = 'A empresa é obrigatória.';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Um e-mail válido é obrigatório.';
        }

        if (empty($data['phone']) || !is_string($data['phone'])) {
            $errors['phone'] = 'O telefone é obrigatório.';
        }

        if (!isset($data['value']) || !is_numeric($data['value']) || $data['value'] <= 0) {
            $errors['value'] = 'O valor é obrigatório e deve ser um número positivo.';
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

        if (array_key_exists('userId', $data) && (!is_int($data['userId']) || $data['userId'] <= 0)) {
            $errors['userId'] = 'userId deve ser um inteiro positivo.';
        }

        if (array_key_exists('stageId', $data) && (!is_int($data['stageId']) || $data['stageId'] <= 0)) {
            $errors['stageId'] = 'stageId deve ser um inteiro positivo.';
        }

        if (array_key_exists('name', $data) && empty($data['name'])) {
            $errors['name'] = 'O nome não pode ser vazio.';
        }

        if (array_key_exists('company', $data) && empty($data['company'])) {
            $errors['company'] = 'A empresa não pode ser vazia.';
        }

        if (array_key_exists('email', $data) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }

        if (array_key_exists('phone', $data) && empty($data['phone'])) {
            $errors['phone'] = 'O telefone não pode ser vazio.';
        }

        if (array_key_exists('value', $data) && (!is_numeric($data['value']) || $data['value'] <= 0)) {
            $errors['value'] = 'O valor deve ser um número positivo.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // atualizaria no banco
        return $data;
    }
}
