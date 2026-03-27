<?php

namespace App\Tests\Service;

use App\Entity\Stage;
use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use App\Repository\StageRepository;
use App\Service\StageService;
use PHPUnit\Framework\TestCase;

class StageServiceTest extends TestCase
{
    private StageRepository $repo;
    private StageService $service;

    protected function setUp(): void
    {
        $this->repo    = $this->createStub(StageRepository::class);
        $this->service = new StageService($this->repo);
    }

    public function testFindAllReturnsMappedArray(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->repo->method('findAllActive')->willReturn([$stage]);

        $result = $this->service->findAll();

        $this->assertCount(1, $result);
        $this->assertSame('Prospecção', $result[0]['name']);
    }

    public function testCreateWithEmptyNameThrowsValidationException(): void
    {
        try {
            $this->service->create([]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('name', $e->getErrors());
            return;
        }

        $this->fail('ValidationException was not thrown');
    }

    public function testCreateCallsSaveAndReturnsArray(): void
    {
        $repo = $this->createMock(StageRepository::class);
        $repo->expects($this->once())->method('save');

        $result = (new StageService($repo))->create(['name' => 'Qualificação']);

        $this->assertSame('Qualificação', $result['name']);
    }

    public function testUpdateThrowsEntityNotFoundExceptionForUnknownId(): void
    {
        $this->repo->method('findById')->willReturn(null);

        $this->expectException(EntityNotFoundException::class);

        $this->service->update(99, ['name' => 'X']);
    }

    public function testUpdateWithValidDataCallsSaveAndReturnsUpdatedArray(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');

        $repo = $this->createMock(StageRepository::class);
        $repo->method('findById')->willReturn($stage);
        $repo->expects($this->once())->method('save');

        $result = (new StageService($repo))->update(1, ['name' => 'Negociação']);

        $this->assertSame('Negociação', $result['name']);
    }

    public function testDeleteThrowsEntityNotFoundExceptionForUnknownId(): void
    {
        $this->repo->method('findById')->willReturn(null);

        $this->expectException(EntityNotFoundException::class);

        $this->service->delete(99);
    }

    public function testDeleteThrowsEntityNotFoundExceptionForAlreadyDeletedStage(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $stage->setDeletedAt(new \DateTimeImmutable());
        $this->repo->method('findById')->willReturn($stage);

        $this->expectException(EntityNotFoundException::class);

        $this->service->delete(1);
    }

    public function testDeleteSetsDeletedAtAndCallsSave(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');

        $repo = $this->createMock(StageRepository::class);
        $repo->method('findById')->willReturn($stage);
        $repo->expects($this->once())->method('save');

        (new StageService($repo))->delete(1);

        $this->assertNotNull($stage->getDeletedAt());
    }
}
