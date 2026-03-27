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

    public function testGetReturnsNotFoundWhenTableIsEmpty(): void
    {
        $this->json('GET', '/lead');

        $this->assertSame(404, $this->statusCode());
    }

    public function testGetReturnsLeadsWhenDataExists(): void
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

        $response = $this->json('GET', '/lead');

        $this->assertSame(200, $this->statusCode());
        $this->assertCount(1, $response['data']);
        $this->assertSame('João', $response['data'][0]['name']);
    }

    public function testPostCreatesLeadAndReturns201(): void
    {
        $response = $this->json('POST', '/lead', $this->validLead());

        $this->assertSame(201, $this->statusCode());
        $this->assertSame('João Silva', $response['data']['name']);
        $this->assertSame('joao@tech.com', $response['data']['email']);
        $this->assertSame('1500.00', $response['data']['value']);
    }

    public function testPostWithAllFieldsMissingReturnsAllErrors(): void
    {
        $response = $this->json('POST', '/lead', []);

        $this->assertSame(422, $this->statusCode());
        $errors = $response['errors'];
        $this->assertArrayHasKey('userId', $errors);
        $this->assertArrayHasKey('stageId', $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('value', $errors);
    }

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
        $this->assertSame('João Atualizado', $response['data']['name']);
        $this->assertSame('2500.00', $response['data']['value']);
    }

    public function testDeleteSoftDeletesLeadAndReturns204(): void
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

        $this->json('DELETE', "/lead/{$lead->getId()}");

        $this->assertSame(204, $this->statusCode());

        $this->em->clear();
        $found = $this->em->find(Lead::class, $lead->getId());
        $this->assertNotNull($found->getDeletedAt());
    }

    public function testDeleteReturnsNotFoundForMissingId(): void
    {
        $this->json('DELETE', '/lead/99999');

        $this->assertSame(404, $this->statusCode());
    }

    public function testDeletedLeadIsExcludedFromList(): void
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

        $this->json('DELETE', "/lead/{$lead->getId()}");
        $this->assertSame(204, $this->statusCode());

        $this->json('GET', '/lead');
        $this->assertSame(404, $this->statusCode());
    }
}
