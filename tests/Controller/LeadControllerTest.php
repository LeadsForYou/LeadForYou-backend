<?php

namespace App\Tests\Controller;

use App\Entity\Lead;
use App\Entity\Stage;
use App\Entity\User;
use App\Tests\Integration\IntegrationTestCase;

class LeadControllerTest extends IntegrationTestCase
{
    private User $user;
    private Stage $stage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->setName('Responsável');
        $this->user->setEmail('resp@email.com');
        $this->user->setPassword('hashed');
        $this->user->setRole('admin');
        $this->em->persist($this->user);

        $this->stage = new Stage();
        $this->stage->setName('Prospecção');
        $this->em->persist($this->stage);

        $this->em->flush();
    }

    public function testListReturnsNotFoundWhenEmpty(): void
    {
        $this->json('GET', '/lead');

        $this->assertSame(404, $this->statusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function testCreateReturnsValidationErrorWithoutBody(): void
    {
        $this->json('POST', '/lead');

        $this->assertSame(422, $this->statusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testCreateReturnsCreatedWithValidBody(): void
    {
        $data = $this->json('POST', '/lead', [
            'userId'  => $this->user->getId(),
            'stageId' => $this->stage->getId(),
            'name'    => 'João Silva',
            'company' => 'Tech Ltda',
            'email'   => 'joao@tech.com',
            'phone'   => '85999999999',
            'value'   => '1500.00',
        ]);

        $this->assertSame(201, $this->statusCode());
        $this->assertArrayHasKey('name', $data['data']);
        $this->assertArrayHasKey('email', $data['data']);
        $this->assertArrayHasKey('company', $data['data']);
        $this->assertArrayHasKey('value', $data['data']);
    }

    public function testUpdateWithPatchMethod(): void
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

        $data = $this->json('PATCH', "/lead/{$lead->getId()}", [
            'name'  => 'João Atualizado',
            'value' => '2000.00',
        ]);

        $this->assertSame(200, $this->statusCode());
        $this->assertArrayHasKey('name', $data['data']);
        $this->assertArrayHasKey('value', $data['data']);
    }

    public function testUpdateReturnsNotFoundForMissingId(): void
    {
        $this->json('PATCH', '/lead/99999', ['name' => 'X']);

        $this->assertSame(404, $this->statusCode());
    }
}
