<?php

namespace App\Service;

use App\Entity\Stage;
use App\Exception\EntityNotFoundException;
use App\Repository\StageRepository;
use App\Validator\Validator;

class StageService
{
    public function __construct(private readonly StageRepository $repo) {}

    public function findAll(): array
    {
        return array_map(fn(Stage $s) => $this->toArray($s), $this->repo->findAll());
    }

    public function create(array $data): array
    {
        (new Validator($data))
            ->required('name', 'O nome é obrigatório.')
            ->throw();

        $stage = new Stage();
        $stage->setName($data['name']);

        $this->repo->save($stage);

        return $this->toArray($stage);
    }

    public function update(int $id, array $data): array
    {
        $stage = $this->repo->findById($id);

        if ($stage === null) {
            throw new EntityNotFoundException("Estágio {$id} não encontrado.");
        }

        (new Validator($data))
            ->notEmpty('name', 'O nome não pode ser vazio.')
            ->throw();

        if (!empty($data['name'])) {
            $stage->setName($data['name']);
        }

        $stage->setUpdatedAt(new \DateTimeImmutable());
        $this->repo->save($stage);

        return $this->toArray($stage);
    }

    private function toArray(Stage $stage): array
    {
        return [
            'id'   => $stage->getId(),
            'name' => $stage->getName(),
        ];
    }
}
