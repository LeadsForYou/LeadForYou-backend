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

    public function testListReturnsStagesWhenDataExists(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $data = $this->json('GET', '/stage');

        $this->assertSame(200, $this->statusCode());
        $this->assertCount(1, $data['data']);
        $this->assertSame('Prospecção', $data['data'][0]['name']);
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

    public function testDeleteReturnsNoContent(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $this->json('DELETE', "/stage/{$stage->getId()}");

        $this->assertSame(204, $this->statusCode());
    }

    public function testDeleteReturnsNotFoundForMissingId(): void
    {
        $this->json('DELETE', '/stage/99999');

        $this->assertSame(404, $this->statusCode());
    }
}
