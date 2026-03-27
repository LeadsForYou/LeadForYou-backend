<?php

namespace App\Tests\Integration;

use App\Entity\User;

class UserIntegrationTest extends IntegrationTestCase
{
    private array $validUser = [
        'name'     => 'João Silva',
        'email'    => 'joao@email.com',
        'password' => '12345678',
        'role'     => 'admin',
    ];

    public function testGetReturnsNotFoundWhenTableIsEmpty(): void
    {
        $this->json('GET', '/user');

        $this->assertSame(404, $this->statusCode());
    }

    public function testGetReturnsUsersWhenDataExists(): void
    {
        $user = new User();
        $user->setName('João Silva');
        $user->setEmail('joao@email.com');
        $user->setPassword('hashed');
        $user->setRole('admin');
        $this->em->persist($user);
        $this->em->flush();

        $response = $this->json('GET', '/user');

        $this->assertSame(200, $this->statusCode());
        $this->assertCount(1, $response['data']);
        $this->assertSame('João Silva', $response['data'][0]['name']);
    }

    public function testPostCreatesUserAndReturns201(): void
    {
        $response = $this->json('POST', '/user', $this->validUser);

        $this->assertSame(201, $this->statusCode());
        $this->assertSame('João Silva', $response['data']['name']);
        $this->assertSame('joao@email.com', $response['data']['email']);
        $this->assertSame('admin', $response['data']['role']);
    }

    public function testPostWithAllFieldsMissingReturnsAllErrors(): void
    {
        $response = $this->json('POST', '/user', []);

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('name', $response['errors']);
        $this->assertArrayHasKey('email', $response['errors']);
        $this->assertArrayHasKey('password', $response['errors']);
        $this->assertArrayHasKey('role', $response['errors']);
    }

    public function testPatchUpdatesUserAndReturns200(): void
    {
        $user = new User();
        $user->setName('João');
        $user->setEmail('joao@email.com');
        $user->setPassword('hashed');
        $user->setRole('admin');
        $this->em->persist($user);
        $this->em->flush();

        $response = $this->json('PATCH', "/user/{$user->getId()}", [
            'name'  => 'João Atualizado',
            'email' => 'novo@email.com',
        ]);

        $this->assertSame(200, $this->statusCode());
        $this->assertSame('João Atualizado', $response['data']['name']);
        $this->assertSame('novo@email.com', $response['data']['email']);
    }

    public function testDeleteSoftDeletesUserAndReturns204(): void
    {
        $user = new User();
        $user->setName('João');
        $user->setEmail('joao@email.com');
        $user->setPassword('hashed');
        $user->setRole('admin');
        $this->em->persist($user);
        $this->em->flush();

        $this->json('DELETE', "/user/{$user->getId()}");

        $this->assertSame(204, $this->statusCode());

        $this->em->clear();
        $found = $this->em->find(User::class, $user->getId());
        $this->assertNotNull($found->getDeletedAt());
    }

    public function testDeleteReturnsNotFoundForMissingId(): void
    {
        $this->json('DELETE', '/user/99999');

        $this->assertSame(404, $this->statusCode());
    }

    public function testDeletedUserIsExcludedFromList(): void
    {
        $user = new User();
        $user->setName('João');
        $user->setEmail('joao@email.com');
        $user->setPassword('hashed');
        $user->setRole('admin');
        $this->em->persist($user);
        $this->em->flush();

        $this->json('DELETE', "/user/{$user->getId()}");
        $this->assertSame(204, $this->statusCode());

        $this->json('GET', '/user');
        $this->assertSame(404, $this->statusCode());
    }
}
