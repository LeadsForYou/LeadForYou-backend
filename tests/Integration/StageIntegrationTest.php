<?php

namespace App\Tests\Integration;

use App\Entity\Stage;

class StageIntegrationTest extends IntegrationTestCase
{
    // -------------------------------------------------------------------------
    // GET /stage
    // -------------------------------------------------------------------------

    public function testGetReturnsNotFoundWhenTableIsEmpty(): void
    {
        $this->json('GET', '/stage');

        $this->assertSame(404, $this->statusCode());
    }

    // -------------------------------------------------------------------------
    // POST /stage
    // -------------------------------------------------------------------------

    public function testPostCreatesStageAndReturns201(): void
    {
        $response = $this->json('POST', '/stage', ['name' => 'Prospecção']);

        $this->assertSame(201, $this->statusCode());
        $this->assertSame('Prospecção', $response['name']);
    }

    public function testPostWithoutBodyReturns422(): void
    {
        $this->json('POST', '/stage');

        $this->assertSame(422, $this->statusCode());
    }

    public function testPostWithEmptyNameReturns422WithErrorKey(): void
    {
        $response = $this->json('POST', '/stage', ['name' => '']);

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('name', $response['errors']);
    }

    // -------------------------------------------------------------------------
    // PATCH /stage/{id}
    // -------------------------------------------------------------------------

    public function testPatchUpdatesStageAndReturns200(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $response = $this->json('PATCH', "/stage/{$stage->getId()}", ['name' => 'Qualificação']);

        $this->assertSame(200, $this->statusCode());
        $this->assertSame('Qualificação', $response['name']);
    }

    public function testPatchWithEmptyNameReturns422(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $response = $this->json('PATCH', "/stage/{$stage->getId()}", ['name' => '']);

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('errors', $response);
    }

    public function testPatchWithNoBodyReturns200(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->em->persist($stage);
        $this->em->flush();

        $this->json('PATCH', "/stage/{$stage->getId()}");

        $this->assertSame(200, $this->statusCode());
    }

    // -------------------------------------------------------------------------
    // Database state
    // -------------------------------------------------------------------------

    public function testStageIsPersistableViaEntityManager(): void
    {
        $stage = new Stage();
        $stage->setName('Fechamento');
        $this->em->persist($stage);
        $this->em->flush();

        $this->em->clear();

        $found = $this->em->find(Stage::class, $stage->getId());

        $this->assertNotNull($found);
        $this->assertSame('Fechamento', $found->getName());
    }

    public function testTruncateResetsAutoIncrement(): void
    {
        $s1 = new Stage();
        $s1->setName('Stage A');
        $this->em->persist($s1);
        $this->em->flush();

        $firstId = $s1->getId();

        $this->truncateTables();
        $this->em->clear();

        $s2 = new Stage();
        $s2->setName('Stage B');
        $this->em->persist($s2);
        $this->em->flush();

        $this->assertSame($firstId, $s2->getId());
    }
}
