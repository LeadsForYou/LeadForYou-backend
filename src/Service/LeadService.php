<?php

namespace App\Service;

use App\Entity\Lead;
use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use App\Repository\LeadRepository;
use App\Repository\StageRepository;
use App\Repository\UserRepository;
use App\Validator\Validator;

class LeadService
{
    public function __construct(
        private readonly LeadRepository  $leadRepo,
        private readonly UserRepository  $userRepo,
        private readonly StageRepository $stageRepo,
    ) {}

    public function findAll(): array
    {
        return array_map(fn(Lead $l) => $this->toArray($l), $this->leadRepo->findAll());
    }

    public function create(array $data): array
    {
        (new Validator($data))
            ->requiredInt('userId', 'O userId é obrigatório e deve ser um inteiro positivo.')
            ->requiredInt('stageId', 'O stageId é obrigatório e deve ser um inteiro positivo.')
            ->required('name', 'O nome é obrigatório.')
            ->required('company', 'A empresa é obrigatória.')
            ->required('email', 'Um e-mail válido é obrigatório.')
            ->email('email', 'Um e-mail válido é obrigatório.')
            ->required('phone', 'O telefone é obrigatório.')
            ->required('value', 'O valor é obrigatório e deve ser um número positivo.')
            ->positiveNumber('value', 'O valor é obrigatório e deve ser um número positivo.')
            ->throw();

        $errors = [];

        $user = $this->userRepo->findById($data['userId']);
        if ($user === null) {
            $errors['userId'] = 'Usuário não encontrado.';
        }

        $stage = $this->stageRepo->findById($data['stageId']);
        if ($stage === null) {
            $errors['stageId'] = 'Estágio não encontrado.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $lead = new Lead();
        $lead->setUser($user);
        $lead->setStage($stage);
        $lead->setName($data['name']);
        $lead->setCompany($data['company']);
        $lead->setEmail($data['email']);
        $lead->setPhone($data['phone']);
        $lead->setValue($data['value']);

        $this->leadRepo->save($lead);

        return $this->toArray($lead);
    }

    public function update(int $id, array $data): array
    {
        $lead = $this->leadRepo->findById($id);

        if ($lead === null) {
            throw new EntityNotFoundException("Lead {$id} não encontrado.");
        }

        (new Validator($data))
            ->positiveInt('userId', 'userId deve ser um inteiro positivo.')
            ->positiveInt('stageId', 'stageId deve ser um inteiro positivo.')
            ->notEmpty('name', 'O nome não pode ser vazio.')
            ->notEmpty('company', 'A empresa não pode ser vazia.')
            ->email('email', 'E-mail inválido.')
            ->notEmpty('phone', 'O telefone não pode ser vazio.')
            ->positiveNumber('value', 'O valor deve ser um número positivo.')
            ->throw();

        if (!empty($data['userId'])) {
            $user = $this->userRepo->findById($data['userId']);
            if ($user === null) {
                throw new ValidationException(['userId' => 'Usuário não encontrado.']);
            }
            $lead->setUser($user);
        }

        if (!empty($data['stageId'])) {
            $stage = $this->stageRepo->findById($data['stageId']);
            if ($stage === null) {
                throw new ValidationException(['stageId' => 'Estágio não encontrado.']);
            }
            $lead->setStage($stage);
        }

        if (!empty($data['name'])) {
            $lead->setName($data['name']);
        }
        if (!empty($data['company'])) {
            $lead->setCompany($data['company']);
        }
        if (!empty($data['email'])) {
            $lead->setEmail($data['email']);
        }
        if (!empty($data['phone'])) {
            $lead->setPhone($data['phone']);
        }
        if (!empty($data['value'])) {
            $lead->setValue($data['value']);
        }

        $lead->setUpdatedAt(new \DateTimeImmutable());
        $this->leadRepo->save($lead);

        return $this->toArray($lead);
    }

    private function toArray(Lead $lead): array
    {
        return [
            'id'      => $lead->getId(),
            'userId'  => $lead->getUser()->getId(),
            'stageId' => $lead->getStage()->getId(),
            'name'    => $lead->getName(),
            'company' => $lead->getCompany(),
            'email'   => $lead->getEmail(),
            'phone'   => $lead->getPhone(),
            'value'   => $lead->getValue(),
        ];
    }
}
