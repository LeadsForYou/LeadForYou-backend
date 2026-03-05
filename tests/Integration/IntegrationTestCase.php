<?php

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em     = static::getContainer()->get('doctrine.orm.entity_manager');

        $this->truncateTables();
    }

    protected function truncateTables(): void
    {
        $conn = $this->em->getConnection();
        $conn->executeStatement('TRUNCATE TABLE leads, users, stages RESTART IDENTITY CASCADE');
    }

    protected function json(string $method, string $uri, array $body = []): array
    {
        $content = empty($body) ? '' : json_encode($body);

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        );

        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }

    protected function statusCode(): int
    {
        return $this->client->getResponse()->getStatusCode();
    }
}
