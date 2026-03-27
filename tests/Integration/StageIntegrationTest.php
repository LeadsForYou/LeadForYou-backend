<?php

namespace App\Tests\Integration;

use App\Entity\Stage;

class StageIntegrationTest extends IntegrationTestCase
{
    public function testGetReturnsNotFoundWhenTableIsEmpty(): void
    {
        $this->json('GET', '/stage');

        $this->assertSame(404, $this->statusCode());
    }

    public function testGetReturnsStagesWhenDataExists(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $response = $this->json('GET', '/stage');

        $this->assertSame(200, $this->statusCode());
        $this->assertCount(1, $response['data']);
        $this->assertSame('Prospecção', $response['data'][0]['name']);
    }

    public function testPostCreatesStageAndReturns201(): void
    {
        $response = $this->json('POST', '/stage', ['name' => 'Prospecção']);

        $this->assertSame(201, $this->statusCode());
        $this->assertSame('Prospecção', $response['data']['name']);
    }

    public function testPostWithoutBodyReturns422(): void
    {
        $this->json('POST', '/stage');

        $this->assertSame(422, $this->statusCode());
    }

    public function testPatchUpdatesStageAndReturns200(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $response = $this->json('PATCH', "/stage/{$stage->getId()}", ['name' => 'Qualificação']);

        $this->assertSame(200, $this->statusCode());
        $this->assertSame('Qualificação', $response['data']['name']);
    }

    public function testDeleteSoftDeletesStageAndReturns204(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $this->json('DELETE', "/stage/{$stage->getId()}");

        $this->assertSame(204, $this->statusCode());

        $this->em->clear();
        $found = $this->em->find(Stage::class, $stage->getId());
        $this->assertNotNull($found->getDeletedAt());
    }

    public function testDeleteReturnsNotFoundForMissingId(): void
    {
        $this->json('DELETE', '/stage/99999');

        $this->assertSame(404, $this->statusCode());
    }

    public function testDeletedStageIsExcludedFromList(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $this->json('DELETE', "/stage/{$stage->getId()}");
        $this->assertSame(204, $this->statusCode());

        $this->json('GET', '/stage');
        $this->assertSame(404, $this->statusCode());
    }
}
