<?php

namespace App\Tests\Controller;

use App\Entity\Stage;
use App\Tests\Integration\IntegrationTestCase;

class StageControllerTest extends IntegrationTestCase
{
    public function testListReturnsNotFoundWhenEmpty(): void
    {
        $this->json('GET', '/stage');

        $this->assertSame(404, $this->statusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function testCreateReturnsValidationErrorWithoutBody(): void
    {
        $this->json('POST', '/stage');

        $this->assertSame(422, $this->statusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testCreateReturnsCreatedWithValidBody(): void
    {
        $data = $this->json('POST', '/stage', ['name' => 'Qualificação']);

        $this->assertSame(201, $this->statusCode());
        $this->assertArrayHasKey('name', $data['data']);
    }

    public function testUpdateWithPatchMethod(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $data = $this->json('PATCH', "/stage/{$stage->getId()}", ['name' => 'Negociação']);

        $this->assertSame(200, $this->statusCode());
        $this->assertArrayHasKey('name', $data['data']);
    }

    public function testUpdateReturnsNotFoundForMissingId(): void
    {
        $this->json('PATCH', '/stage/99999', ['name' => 'X']);

        $this->assertSame(404, $this->statusCode());
    }
}
