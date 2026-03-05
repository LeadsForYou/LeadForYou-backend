<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\Integration\IntegrationTestCase;

class UserControllerTest extends IntegrationTestCase
{
    public function testListReturnsNotFoundWhenEmpty(): void
    {
        $this->json('GET', '/user');

        $this->assertSame(404, $this->statusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function testCreateReturnsValidationErrorWithoutBody(): void
    {
        $this->json('POST', '/user');

        $this->assertSame(422, $this->statusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testCreateReturnsCreatedWithValidBody(): void
    {
        $data = $this->json('POST', '/user', [
            'name'     => 'João Silva',
            'email'    => 'joao@email.com',
            'password' => '12345678',
            'role'     => 'admin',
        ]);

        $this->assertSame(201, $this->statusCode());
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('role', $data);
    }

    public function testUpdateWithPatchMethod(): void
    {
        $user = new User();
        $user->setName('João Silva');
        $user->setEmail('joao@email.com');
        $user->setPassword('hashed');
        $user->setRole('admin');
        $this->em->persist($user);
        $this->em->flush();

        $data = $this->json('PATCH', "/user/{$user->getId()}", [
            'name'  => 'João Atualizado',
            'email' => 'joao.novo@email.com',
        ]);

        $this->assertSame(200, $this->statusCode());
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
    }

    public function testUpdateReturnsNotFoundForMissingId(): void
    {
        $this->json('PATCH', '/user/99999', ['name' => 'X']);

        $this->assertSame(404, $this->statusCode());
    }
}
