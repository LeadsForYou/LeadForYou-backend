<?php

namespace App\Repository;

use App\Entity\Stage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stage::class);
    }

    public function findById(int $id): ?Stage
    {
        return $this->find($id);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function save(Stage $stage): void
    {
        $this->getEntityManager()->persist($stage);
        $this->getEntityManager()->flush();
    }
}
