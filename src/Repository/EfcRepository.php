<?php

namespace App\Repository;

use App\Entity\Dynamic\Efc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Efc>
 */
class EfcRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Efc::class);
    }
    public function findMttcNotEqualMtrappByCodeTrs(string $codeTrs)
    {
        $results = $this->createQueryBuilder('e')
            ->where('e.codetrs = :codeTrs')
            ->setParameter('codeTrs', $codeTrs)
            ->getQuery()
            ->getResult();

        return array_filter($results, function ($item) {
            return round($item->getMttc() + $item->getTimbref(), 3) !== round($item->getMtrapp(), 3);
        });
    }
    public function findFactures(string $coderep, EntityManagerInterface $entityManager): array
    {
           return $entityManager->createQueryBuilder()
            ->select('e')
            ->from(Efc::class, 'e')
            ->where('e.coderep = :coderep')
            ->setParameter('coderep', $coderep)
            ->getQuery()
            ->getResult();
    }
       

    //    /**
    //     * @return Efc[] Returns an array of Efc objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Efc
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
