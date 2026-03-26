<?php

namespace App\Service;

use App\Entity\Lead;
use App\Exception\EntityNotFoundException;
use App\Repository\LeadRepository;
use App\Repository\StageRepository;
use App\Repository\UserRepository;
use App\Validator\Validator;

class LeadService
{
    public function __construct(
        private readonly LeadRepository $leadRepo,
        private readonly UserRepository $userRepo,
        private readonly StageRepository $stageRepo,
    ) {
    }

    public function findAll(): array
    {
        return array_map(fn (Lead $l) => $this->toArray($l), $this->leadRepo->findAllActive());
    }

    public function create(array $data): array
    {
        $user = isset($data['userId']) && is_int($data['userId']) ? $this->userRepo->findById($data['userId']) : null;
        $stage = isset($data['stageId']) && is_int($data['stageId']) ? $this->stageRepo->findById($data['stageId']) : null;

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
            ->check('userId', null !== $user, 'Usuário não encontrado.')
            ->check('stageId', null !== $stage, 'Estágio não encontrado.')
            ->throw();

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

        if (null === $lead) {
            throw new EntityNotFoundException("Lead {$id} não encontrado.");
        }

        $user = isset($data['userId']) && is_int($data['userId']) ? $this->userRepo->findById($data['userId']) : null;
        $stage = isset($data['stageId']) && is_int($data['stageId']) ? $this->stageRepo->findById($data['stageId']) : null;

        (new Validator($data))
            ->positiveInt('userId', 'userId deve ser um inteiro positivo.')
            ->positiveInt('stageId', 'stageId deve ser um inteiro positivo.')
            ->notEmpty('name', 'O nome não pode ser vazio.')
            ->notEmpty('company', 'A empresa não pode ser vazia.')
            ->email('email', 'E-mail inválido.')
            ->notEmpty('phone', 'O telefone não pode ser vazio.')
            ->positiveNumber('value', 'O valor deve ser um número positivo.')
            ->check('userId', null !== $user, 'Usuário não encontrado.')
            ->check('stageId', null !== $stage, 'Estágio não encontrado.')
            ->throw();

        if (null !== $user) {
            $lead->setUser($user);
        }
        if (null !== $stage) {
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

    public function delete(int $id): void
    {
        $lead = $this->leadRepo->findById($id);

        if (null === $lead || null !== $lead->getDeletedAt()) {
            throw new EntityNotFoundException("Lead {$id} não encontrado.");
        }

        $lead->setDeletedAt(new \DateTimeImmutable());
        $this->leadRepo->save($lead);
    }

    private function toArray(Lead $lead): array
    {
        return [
            'id' => $lead->getId(),
            'userId' => $lead->getUser()->getId(),
            'stageId' => $lead->getStage()->getId(),
            'name' => $lead->getName(),
            'company' => $lead->getCompany(),
            'email' => $lead->getEmail(),
            'phone' => $lead->getPhone(),
            'value' => $lead->getValue(),
        ];
    }
}
