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

    // -------------------------------------------------------------------------
    // GET /user
    // -------------------------------------------------------------------------

    public function testGetReturnsNotFoundWhenTableIsEmpty(): void
    {
        $this->json('GET', '/user');

        $this->assertSame(404, $this->statusCode());
    }

    // -------------------------------------------------------------------------
    // POST /user
    // -------------------------------------------------------------------------

    public function testPostCreatesUserAndReturns201(): void
    {
        $response = $this->json('POST', '/user', $this->validUser);

        $this->assertSame(201, $this->statusCode());
        $this->assertSame('João Silva', $response['data']['name']);
        $this->assertSame('joao@email.com', $response['data']['email']);
        $this->assertSame('admin', $response['data']['role']);
    }

    public function testPostWithoutBodyReturns422(): void
    {
        $this->json('POST', '/user');

        $this->assertSame(422, $this->statusCode());
    }

    public function testPostWithInvalidEmailReturns422(): void
    {
        $response = $this->json('POST', '/user', array_merge($this->validUser, ['email' => 'nao-e-email']));

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('email', $response['errors']);
    }

    public function testPostWithShortPasswordReturns422(): void
    {
        $response = $this->json('POST', '/user', array_merge($this->validUser, ['password' => '123']));

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('password', $response['errors']);
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

    // -------------------------------------------------------------------------
    // PATCH /user/{id}
    // -------------------------------------------------------------------------

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

    public function testPatchWithInvalidEmailReturns422(): void
    {
        $user = new User();
        $user->setName('João');
        $user->setEmail('joao@email.com');
        $user->setPassword('hashed');
        $user->setRole('admin');
        $this->em->persist($user);
        $this->em->flush();

        $response = $this->json('PATCH', "/user/{$user->getId()}", ['email' => 'invalido']);

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('email', $response['errors']);
    }

    public function testPatchWithNoBodyReturns200(): void
    {
        $user = new User();
        $user->setName('João');
        $user->setEmail('joao@email.com');
        $user->setPassword('hashed');
        $user->setRole('admin');
        $this->em->persist($user);
        $this->em->flush();

        $this->json('PATCH', "/user/{$user->getId()}");

        $this->assertSame(200, $this->statusCode());
    }

    // -------------------------------------------------------------------------
    // Database state
    // -------------------------------------------------------------------------

    public function testUserIsPersistableViaEntityManager(): void
    {
        $user = new User();
        $user->setName('Maria');
        $user->setEmail('maria@email.com');
        $user->setPassword('hashed123');
        $user->setRole('user');
        $this->em->persist($user);
        $this->em->flush();

        $this->em->clear();

        $found = $this->em->find(User::class, $user->getId());

        $this->assertNotNull($found);
        $this->assertSame('Maria', $found->getName());
        $this->assertSame('maria@email.com', $found->getEmail());
        $this->assertTrue($found->isActive());
    }
}
