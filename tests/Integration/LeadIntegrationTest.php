<?php

namespace App\Tests\Integration;

use App\Entity\Lead;
use App\Entity\Stage;
use App\Entity\User;

class LeadIntegrationTest extends IntegrationTestCase
{
    private Stage $stage;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stage = new Stage();
        $this->stage->setName('Prospecção');
        $this->em->persist($this->stage);

        $this->user = new User();
        $this->user->setName('Responsável');
        $this->user->setEmail('resp@email.com');
        $this->user->setPassword('hashed');
        $this->user->setRole('admin');
        $this->em->persist($this->user);

        $this->em->flush();
    }

    private function validLead(): array
    {
        return [
            'userId'  => $this->user->getId(),
            'stageId' => $this->stage->getId(),
            'name'    => 'João Silva',
            'company' => 'Tech Ltda',
            'email'   => 'joao@tech.com',
            'phone'   => '85999999999',
            'value'   => '1500.00',
        ];
    }

    // -------------------------------------------------------------------------
    // GET /lead
    // -------------------------------------------------------------------------

    public function testGetReturnsNotFoundWhenTableIsEmpty(): void
    {
        $this->json('GET', '/lead');

        $this->assertSame(404, $this->statusCode());
    }

    // -------------------------------------------------------------------------
    // POST /lead
    // -------------------------------------------------------------------------

    public function testPostCreatesLeadAndReturns201(): void
    {
        $response = $this->json('POST', '/lead', $this->validLead());

        $this->assertSame(201, $this->statusCode());
        $this->assertSame('João Silva', $response['name']);
        $this->assertSame('Tech Ltda', $response['company']);
        $this->assertSame('joao@tech.com', $response['email']);
        $this->assertSame('1500.00', $response['value']);
    }

    public function testPostWithoutBodyReturns422(): void
    {
        $this->json('POST', '/lead');

        $this->assertSame(422, $this->statusCode());
    }

    public function testPostWithInvalidEmailReturns422(): void
    {
        $response = $this->json('POST', '/lead', array_merge($this->validLead(), ['email' => 'invalido']));

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('email', $response['errors']);
    }

    public function testPostWithNegativeValueReturns422(): void
    {
        $response = $this->json('POST', '/lead', array_merge($this->validLead(), ['value' => '-100']));

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('value', $response['errors']);
    }

    public function testPostWithInvalidUserIdReturns422(): void
    {
        $response = $this->json('POST', '/lead', array_merge($this->validLead(), ['userId' => 0]));

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('userId', $response['errors']);
    }

    public function testPostWithAllFieldsMissingReturnsAllErrors(): void
    {
        $response = $this->json('POST', '/lead', []);

        $this->assertSame(422, $this->statusCode());
        $errors = $response['errors'];
        $this->assertArrayHasKey('userId', $errors);
        $this->assertArrayHasKey('stageId', $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('company', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('phone', $errors);
        $this->assertArrayHasKey('value', $errors);
    }

    // -------------------------------------------------------------------------
    // PATCH /lead/{id}
    // -------------------------------------------------------------------------

    public function testPatchUpdatesLeadAndReturns200(): void
    {
        $lead = new Lead();
        $lead->setUser($this->user);
        $lead->setStage($this->stage);
        $lead->setName('João');
        $lead->setCompany('Empresa');
        $lead->setEmail('joao@email.com');
        $lead->setPhone('85999999999');
        $lead->setValue('1000.00');
        $this->em->persist($lead);
        $this->em->flush();

        $response = $this->json('PATCH', "/lead/{$lead->getId()}", [
            'name'  => 'João Atualizado',
            'value' => '2500.00',
        ]);

        $this->assertSame(200, $this->statusCode());
        $this->assertSame('João Atualizado', $response['name']);
        $this->assertSame('2500.00', $response['value']);
    }

    public function testPatchWithInvalidEmailReturns422(): void
    {
        $lead = new Lead();
        $lead->setUser($this->user);
        $lead->setStage($this->stage);
        $lead->setName('João');
        $lead->setCompany('Empresa');
        $lead->setEmail('joao@email.com');
        $lead->setPhone('85999999999');
        $lead->setValue('1000.00');
        $this->em->persist($lead);
        $this->em->flush();

        $response = $this->json('PATCH', "/lead/{$lead->getId()}", ['email' => 'invalido']);

        $this->assertSame(422, $this->statusCode());
        $this->assertArrayHasKey('email', $response['errors']);
    }

    // -------------------------------------------------------------------------
    // Database state
    // -------------------------------------------------------------------------

    public function testLeadIsPersistableViaEntityManager(): void
    {
        $lead = new Lead();
        $lead->setUser($this->user);
        $lead->setStage($this->stage);
        $lead->setName('Maria');
        $lead->setCompany('Corp SA');
        $lead->setEmail('maria@corp.com');
        $lead->setPhone('11988887777');
        $lead->setValue('5000.00');
        $this->em->persist($lead);
        $this->em->flush();

        $this->em->clear();

        $found = $this->em->find(Lead::class, $lead->getId());

        $this->assertNotNull($found);
        $this->assertSame('Maria', $found->getName());
        $this->assertSame('5000.00', $found->getValue());
        $this->assertSame($this->stage->getId(), $found->getStage()->getId());
        $this->assertSame($this->user->getId(), $found->getUser()->getId());
    }

    public function testLeadForeignKeysReferenceCorrectEntities(): void
    {
        $lead = new Lead();
        $lead->setUser($this->user);
        $lead->setStage($this->stage);
        $lead->setName('Test FK');
        $lead->setCompany('Corp');
        $lead->setEmail('fk@test.com');
        $lead->setPhone('11999998888');
        $lead->setValue('100.00');
        $this->em->persist($lead);
        $this->em->flush();

        $this->em->clear();

        $found = $this->em->find(Lead::class, $lead->getId());

        $this->assertSame('Prospecção', $found->getStage()->getName());
        $this->assertSame('Responsável', $found->getUser()->getName());
    }
}
