<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\EntityNotFoundException;
use App\Repository\UserRepository;
use App\Validator\Validator;

class UserService
{
    public function __construct(private readonly UserRepository $repo) {}

    public function findAll(): array
    {
        return array_map(fn(User $user) => $this->toArray($user), $this->repo->findAll());
    }

    public function create(array $data): array
    {
        (new Validator($data))
            ->required('name', 'O nome é obrigatório.')
            ->required('email', 'Um e-mail válido é obrigatório.')
            ->email('email', 'Um e-mail válido é obrigatório.')
            ->required('password', 'A senha deve ter no mínimo 8 caracteres.')
            ->minLength('password', 8, 'A senha deve ter no mínimo 8 caracteres.')
            ->required('role', 'O perfil é obrigatório.')
            ->throw();

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setRole($data['role']);

        $this->repo->save($user);

        return $this->toArray($user);
    }

    public function update(int $id, array $data): array
    {
        $user = $this->repo->findById($id);

        if ($user === null) {
            throw new EntityNotFoundException("Usuário {$id} não encontrado.");
        }

        (new Validator($data))
            ->notEmpty('name', 'O nome não pode ser vazio.')
            ->email('email', 'E-mail inválido.')
            ->minLength('password', 8, 'A senha deve ter no mínimo 8 caracteres.')
            ->notEmpty('role', 'O perfil não pode ser vazio.')
            ->throw();

        if (!empty($data['name'])) {
            $user->setName($data['name']);
        }
        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (!empty($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        }
        if (!empty($data['role'])) {
            $user->setRole($data['role']);
        }

        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->repo->save($user);

        return $this->toArray($user);
    }

    private function toArray(User $user): array
    {
        return [
            'id'     => $user->getId(),
            'name'   => $user->getName(),
            'email'  => $user->getEmail(),
            'role'   => $user->getRole(),
            'active' => $user->isActive(),
        ];
    }
}
