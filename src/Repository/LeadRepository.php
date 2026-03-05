<?php

namespace App\Repository;

use App\Entity\Lead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LeadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lead::class);
    }

    public function findById(int $id): ?Lead
    {
        return $this->find($id);
    }

    public function save(Lead $lead): void
    {
        $this->getEntityManager()->persist($lead);
        $this->getEntityManager()->flush();
    }
}
