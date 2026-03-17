<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserRepository $repo;
    private UserService $service;

    private array $validData = [
        'name'     => 'João Silva',
        'email'    => 'joao@email.com',
        'password' => '12345678',
        'role'     => 'admin',
    ];

    protected function setUp(): void
    {
        $this->repo    = $this->createStub(UserRepository::class);
        $this->service = new UserService($this->repo);
    }

    // -------------------------------------------------------------------------
    // findAll
    // -------------------------------------------------------------------------

    public function testFindAllReturnsEmptyArray(): void
    {
        $this->repo->method('findAll')->willReturn([]);

        $this->assertSame([], $this->service->findAll());
    }

    // -------------------------------------------------------------------------
    // create – validation
    // -------------------------------------------------------------------------

    public function testCreateWithEmptyBodyThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create([]);
    }

    public function testCreateWithMissingNameThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($this->validData, ['name' => '']));
    }

    public function testCreateWithInvalidEmailThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($this->validData, ['email' => 'nao-e-email']));
    }

    public function testCreateWithShortPasswordThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($this->validData, ['password' => '123']));
    }

    public function testCreateWithMissingRoleThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($this->validData, ['role' => '']));
    }

    public function testCreateErrorsContainAllInvalidFields(): void
    {
        try {
            $this->service->create([]);
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('name', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('password', $errors);
            $this->assertArrayHasKey('role', $errors);
            return;
        }

        $this->fail('ValidationException was not thrown');
    }

    // -------------------------------------------------------------------------
    // create – happy path
    // -------------------------------------------------------------------------

    public function testCreateCallsSaveAndReturnsArray(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $repo->expects($this->once())->method('save');

        $result = (new UserService($repo))->create($this->validData);

        $this->assertSame('João Silva', $result['name']);
        $this->assertSame('joao@email.com', $result['email']);
        $this->assertSame('admin', $result['role']);
    }

    // -------------------------------------------------------------------------
    // update – not found
    // -------------------------------------------------------------------------

    public function testUpdateThrowsEntityNotFoundExceptionForUnknownId(): void
    {
        $this->repo->method('findById')->willReturn(null);

        $this->expectException(EntityNotFoundException::class);

        $this->service->update(99, ['name' => 'X']);
    }

    // -------------------------------------------------------------------------
    // update – validation
    // -------------------------------------------------------------------------

    public function testUpdateWithEmptyNameThrowsValidationException(): void
    {
        $this->repo->method('findById')->willReturn(new User());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['name' => '']);
    }

    public function testUpdateWithInvalidEmailThrowsValidationException(): void
    {
        $this->repo->method('findById')->willReturn(new User());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['email' => 'invalido']);
    }

    public function testUpdateWithShortPasswordThrowsValidationException(): void
    {
        $this->repo->method('findById')->willReturn(new User());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['password' => '123']);
    }

    public function testUpdateWithEmptyRoleThrowsValidationException(): void
    {
        $this->repo->method('findById')->willReturn(new User());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['role' => '']);
    }

    // -------------------------------------------------------------------------
    // update – happy path
    // -------------------------------------------------------------------------

    public function testUpdateCallsSaveAndReturnsUpdatedArray(): void
    {
        $user = new User();
        $user->setName('Antigo');
        $user->setEmail('antigo@email.com');
        $user->setPassword('hashed');
        $user->setRole('user');

        $repo = $this->createMock(UserRepository::class);
        $repo->method('findById')->willReturn($user);
        $repo->expects($this->once())->method('save');

        $result = (new UserService($repo))->update(1, ['name' => 'Novo Nome', 'email' => 'novo@email.com']);

        $this->assertSame('Novo Nome', $result['name']);
        $this->assertSame('novo@email.com', $result['email']);
    }

    public function testUpdateWithNoDataReturnsCurrentState(): void
    {
        $user = new User();
        $user->setName('João');
        $user->setEmail('joao@email.com');
        $user->setPassword('hashed');
        $user->setRole('admin');
        $this->repo->method('findById')->willReturn($user);

        $result = $this->service->update(1, []);

        $this->assertSame('João', $result['name']);
    }
}
