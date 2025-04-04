<?php

namespace App\Repository;

use App\Entity\Global\Paramaitre;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paramaitre>
 */
class ParametreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paramaitre::class);
    }
    public function findByNonClotureAndCode(string $code): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cloturer = :cloturer')
            ->andWhere('p.code = :code')
            ->setParameter('cloturer', false)
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult();
    }

}