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

    // -------------------------------------------------------------------------
    // findAll
    // -------------------------------------------------------------------------

    public function testFindAllReturnsEmptyArray(): void
    {
        $this->repo->method('findAll')->willReturn([]);

        $this->assertSame([], $this->service->findAll());
    }

    public function testFindAllReturnsMappedArray(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->repo->method('findAll')->willReturn([$stage]);

        $result = $this->service->findAll();

        $this->assertCount(1, $result);
        $this->assertSame('Prospecção', $result[0]['name']);
    }

    // -------------------------------------------------------------------------
    // create – validation
    // -------------------------------------------------------------------------

    public function testCreateWithEmptyNameThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(['name' => '']);
    }

    public function testCreateWithMissingNameThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create([]);
    }

    public function testCreateErrorContainsNameKey(): void
    {
        try {
            $this->service->create([]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('name', $e->getErrors());
            return;
        }

        $this->fail('ValidationException was not thrown');
    }

    // -------------------------------------------------------------------------
    // create – happy path
    // -------------------------------------------------------------------------

    public function testCreateCallsSaveAndReturnsArray(): void
    {
        $repo = $this->createMock(StageRepository::class);
        $repo->expects($this->once())->method('save');

        $result = (new StageService($repo))->create(['name' => 'Qualificação']);

        $this->assertSame('Qualificação', $result['name']);
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
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->repo->method('findById')->willReturn($stage);

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['name' => '']);
    }

    public function testUpdateErrorContainsNameKey(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->repo->method('findById')->willReturn($stage);

        try {
            $this->service->update(1, ['name' => '']);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('name', $e->getErrors());
            return;
        }

        $this->fail('ValidationException was not thrown');
    }

    // -------------------------------------------------------------------------
    // update – happy path
    // -------------------------------------------------------------------------

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

    public function testUpdateWithNoDataReturnsCurrentState(): void
    {
        $stage = new Stage();
        $stage->setName('Prospecção');
        $this->repo->method('findById')->willReturn($stage);

        $result = $this->service->update(1, []);

        $this->assertSame('Prospecção', $result['name']);
    }
}
