<?php

namespace App\Tests\Service;

use App\Entity\Lead;
use App\Entity\Stage;
use App\Entity\User;
use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use App\Repository\LeadRepository;
use App\Repository\StageRepository;
use App\Repository\UserRepository;
use App\Service\LeadService;
use PHPUnit\Framework\TestCase;

class LeadServiceTest extends TestCase
{
    private LeadRepository  $leadRepo;
    private UserRepository  $userRepo;
    private StageRepository $stageRepo;
    private LeadService     $service;

    private array $validData = [
        'userId'  => 1,
        'stageId' => 2,
        'name'    => 'João Silva',
        'company' => 'Tech Ltda',
        'email'   => 'joao@tech.com',
        'phone'   => '85999999999',
        'value'   => '1500.00',
    ];

    protected function setUp(): void
    {
        $this->leadRepo  = $this->createStub(LeadRepository::class);
        $this->userRepo  = $this->createStub(UserRepository::class);
        $this->stageRepo = $this->createStub(StageRepository::class);
        $this->service   = new LeadService($this->leadRepo, $this->userRepo, $this->stageRepo);
    }

    // -------------------------------------------------------------------------
    // findAll
    // -------------------------------------------------------------------------

    public function testFindAllReturnsEmptyArray(): void
    {
        $this->leadRepo->method('findAllActive')->willReturn([]);

        $this->assertSame([], $this->service->findAll());
    }

    public function testFindAllReturnsMappedArray(): void
    {
        $lead = $this->existingLead();
        $this->leadRepo->method('findAllActive')->willReturn([$lead]);

        $result = $this->service->findAll();

        $this->assertCount(1, $result);
        $this->assertSame('João', $result[0]['name']);
        $this->assertSame('Corp', $result[0]['company']);
        $this->assertSame('j@j.com', $result[0]['email']);
        $this->assertSame('100.00', $result[0]['value']);
    }

    // -------------------------------------------------------------------------
    // create – validation (format)
    // -------------------------------------------------------------------------

    public function testCreateWithEmptyBodyThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create([]);
    }

    public function testCreateErrorsContainAllInvalidFields(): void
    {
        try {
            $this->service->create([]);
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('userId', $errors);
            $this->assertArrayHasKey('stageId', $errors);
            $this->assertArrayHasKey('name', $errors);
            $this->assertArrayHasKey('company', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('phone', $errors);
            $this->assertArrayHasKey('value', $errors);
            return;
        }

        $this->fail('ValidationException was not thrown');
    }

    public function testCreateWithInvalidUserIdThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($this->validData, ['userId' => 0]));
    }

    public function testCreateWithInvalidEmailThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($this->validData, ['email' => 'invalido']));
    }

    public function testCreateWithNegativeValueThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($this->validData, ['value' => '-100']));
    }

    public function testCreateWithZeroValueThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($this->validData, ['value' => '0']));
    }

    // -------------------------------------------------------------------------
    // create – validation (FK existence)
    // -------------------------------------------------------------------------

    public function testCreateWithUserNotFoundThrowsValidationException(): void
    {
        $this->userRepo->method('findById')->willReturn(null);
        $this->stageRepo->method('findById')->willReturn(new Stage());

        try {
            $this->service->create($this->validData);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('userId', $e->getErrors());
            return;
        }

        $this->fail('ValidationException was not thrown');
    }

    public function testCreateWithStageNotFoundThrowsValidationException(): void
    {
        $this->userRepo->method('findById')->willReturn(new User());
        $this->stageRepo->method('findById')->willReturn(null);

        try {
            $this->service->create($this->validData);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('stageId', $e->getErrors());
            return;
        }

        $this->fail('ValidationException was not thrown');
    }

    // -------------------------------------------------------------------------
    // create – happy path
    // -------------------------------------------------------------------------

    public function testCreateCallsSaveAndReturnsArray(): void
    {
        $user = new User();
        $user->setName('Test');
        $user->setEmail('t@t.com');
        $user->setPassword('h');
        $user->setRole('user');

        $stage = new Stage();
        $stage->setName('Etapa');

        $leadRepo  = $this->createMock(LeadRepository::class);
        $userRepo  = $this->createStub(UserRepository::class);
        $stageRepo = $this->createStub(StageRepository::class);

        $userRepo->method('findById')->willReturn($user);
        $stageRepo->method('findById')->willReturn($stage);
        $leadRepo->expects($this->once())->method('save');

        $result = (new LeadService($leadRepo, $userRepo, $stageRepo))->create($this->validData);

        $this->assertSame('João Silva', $result['name']);
        $this->assertSame('Tech Ltda', $result['company']);
        $this->assertSame('joao@tech.com', $result['email']);
        $this->assertSame('1500.00', $result['value']);
    }

    // -------------------------------------------------------------------------
    // update – not found
    // -------------------------------------------------------------------------

    public function testUpdateThrowsEntityNotFoundExceptionForUnknownId(): void
    {
        $this->leadRepo->method('findById')->willReturn(null);

        $this->expectException(EntityNotFoundException::class);

        $this->service->update(99, ['name' => 'X']);
    }

    // -------------------------------------------------------------------------
    // update – validation
    // -------------------------------------------------------------------------

    private function existingLead(): Lead
    {
        $user = new User();
        $user->setName('U');
        $user->setEmail('u@u.com');
        $user->setPassword('h');
        $user->setRole('user');

        $stage = new Stage();
        $stage->setName('S');

        $lead = new Lead();
        $lead->setUser($user);
        $lead->setStage($stage);
        $lead->setName('João');
        $lead->setCompany('Corp');
        $lead->setEmail('j@j.com');
        $lead->setPhone('11999999999');
        $lead->setValue('100.00');

        return $lead;
    }

    public function testUpdateWithInvalidUserIdThrowsValidationException(): void
    {
        $this->leadRepo->method('findById')->willReturn($this->existingLead());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['userId' => -1]);
    }

    public function testUpdateWithInvalidStageIdThrowsValidationException(): void
    {
        $this->leadRepo->method('findById')->willReturn($this->existingLead());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['stageId' => 0]);
    }

    public function testUpdateWithEmptyNameThrowsValidationException(): void
    {
        $this->leadRepo->method('findById')->willReturn($this->existingLead());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['name' => '']);
    }

    public function testUpdateWithEmptyCompanyThrowsValidationException(): void
    {
        $this->leadRepo->method('findById')->willReturn($this->existingLead());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['company' => '']);
    }

    public function testUpdateWithInvalidEmailThrowsValidationException(): void
    {
        $this->leadRepo->method('findById')->willReturn($this->existingLead());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['email' => 'invalido']);
    }

    public function testUpdateWithEmptyPhoneThrowsValidationException(): void
    {
        $this->leadRepo->method('findById')->willReturn($this->existingLead());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['phone' => '']);
    }

    public function testUpdateWithNegativeValueThrowsValidationException(): void
    {
        $this->leadRepo->method('findById')->willReturn($this->existingLead());

        $this->expectException(ValidationException::class);

        $this->service->update(1, ['value' => '-50']);
    }

    // -------------------------------------------------------------------------
    // update – happy path
    // -------------------------------------------------------------------------

    public function testUpdateCallsSaveAndReturnsUpdatedArray(): void
    {
        $lead = $this->existingLead();

        $leadRepo  = $this->createMock(LeadRepository::class);
        $leadRepo->method('findById')->willReturn($lead);
        $leadRepo->expects($this->once())->method('save');

        $result = (new LeadService($leadRepo, $this->userRepo, $this->stageRepo))
            ->update(1, ['name' => 'João Atualizado', 'value' => '2000.00']);

        $this->assertSame('João Atualizado', $result['name']);
        $this->assertSame('2000.00', $result['value']);
    }

    public function testUpdateWithNoDataReturnsCurrentState(): void
    {
        $lead = $this->existingLead();
        $this->leadRepo->method('findById')->willReturn($lead);

        $result = $this->service->update(1, []);

        $this->assertSame('João', $result['name']);
        $this->assertSame('100.00', $result['value']);
    }

    // -------------------------------------------------------------------------
    // delete – not found
    // -------------------------------------------------------------------------

    public function testDeleteThrowsEntityNotFoundExceptionForUnknownId(): void
    {
        $this->leadRepo->method('findById')->willReturn(null);

        $this->expectException(EntityNotFoundException::class);

        $this->service->delete(99);
    }

    public function testDeleteThrowsEntityNotFoundExceptionForAlreadyDeletedLead(): void
    {
        $lead = $this->existingLead();
        $lead->setDeletedAt(new \DateTimeImmutable());
        $this->leadRepo->method('findById')->willReturn($lead);

        $this->expectException(EntityNotFoundException::class);

        $this->service->delete(1);
    }

    // -------------------------------------------------------------------------
    // delete – happy path
    // -------------------------------------------------------------------------

    public function testDeleteSetsDeletedAtAndCallsSave(): void
    {
        $lead = $this->existingLead();

        $leadRepo = $this->createMock(LeadRepository::class);
        $leadRepo->method('findById')->willReturn($lead);
        $leadRepo->expects($this->once())->method('save');

        (new LeadService($leadRepo, $this->userRepo, $this->stageRepo))->delete(1);

        $this->assertNotNull($lead->getDeletedAt());
    }
}
