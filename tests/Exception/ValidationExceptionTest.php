<?php

namespace App\Tests\Exception;

use App\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function testGetErrors(): void
    {
        $errors = ['name' => 'O nome é obrigatório.', 'email' => 'E-mail inválido.'];
        $exception = new ValidationException($errors);

        $this->assertSame($errors, $exception->getErrors());
    }

    public function testMessage(): void
    {
        $exception = new ValidationException([]);

        $this->assertSame('Validation failed', $exception->getMessage());
    }

    public function testIsRuntimeException(): void
    {
        $exception = new ValidationException([]);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
