<?php

namespace App\Validator;

use App\Exception\ValidationException;

class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data) {}

    private function has(string $field): bool
    {
        return array_key_exists($field, $this->data);
    }

    private function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    public function required(string $field, string $message): static
    {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function requiredInt(string $field, string $message): static
    {
        if (!$this->has($field) || !is_int($this->data[$field]) || $this->data[$field] <= 0) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function notEmpty(string $field, string $message): static
    {
        if (!$this->hasError($field) && $this->has($field) && empty($this->data[$field])) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function email(string $field, string $message): static
    {
        if (!$this->hasError($field) && $this->has($field) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function minLength(string $field, int $min, string $message): static
    {
        if (!$this->hasError($field) && $this->has($field) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function positiveInt(string $field, string $message): static
    {
        if (!$this->hasError($field) && $this->has($field) && (!is_int($this->data[$field]) || $this->data[$field] <= 0)) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function positiveNumber(string $field, string $message): static
    {
        if (!$this->hasError($field) && $this->has($field) && (!is_numeric($this->data[$field]) || $this->data[$field] <= 0)) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function throw(): void
    {
        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }
    }
}
