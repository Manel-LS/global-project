<?php

namespace App\Repository;

use App\Entity\Fourchette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fourchette>
 */
class FourchetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fourchette::class);
    }
    public function findAjustement(string $codeart, string $tarif, float $qte): ?float
    {
        $result = $this->createQueryBuilder('f')
            ->andWhere('f.codeart = :codeart')
            ->andWhere('f.ctarif = :tarif')
            ->andWhere('f.qtemin < :qte')
            ->andWhere('f.qtemax >= :qte')
            ->setParameter('codeart', $codeart)
            ->setParameter('tarif', $tarif)
            ->setParameter('qte', $qte)
            ->orderBy('f.qtemin', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
            return $result ? (float) $result->getAjustement() : 0;
        }



    //    /**
    //     * @return Fourchette[] Returns an array of Fourchette objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Fourchette
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
