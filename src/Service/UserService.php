<?php

namespace App\Service;

class UserService
{
    private string $mockDir;

    public function __construct()
    {
        $this->mockDir = __DIR__ . '/../Controller/mock';
    }

    public function findAll(): mixed
    {
        // buscaria do banco
        return json_decode(file_get_contents("{$this->mockDir}/getusers.json"));
    }

    public function create(array $data): mixed
    {
        // salvaria no banco
        return json_decode(file_get_contents("{$this->mockDir}/postuser.json"));
    }

    public function update(int $id, array $data): mixed
    {
        // atualizaria no banco
        return json_decode(file_get_contents("{$this->mockDir}/putUser.json"));
    }
}
