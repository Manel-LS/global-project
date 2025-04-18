<?php

namespace App\Repository;

use App\Entity\LigneWeb;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Cast\String_;

/**
 * @extends ServiceEntityRepository<LigneWeb>
 */
class LigneWebRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneWeb::class);
    }
    public function findOneByLastNummvt(): ?LigneWeb
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.nummvt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
 
    public function getLigneWebNonValide(String $coderep): array
    {
        return $this->createQueryBuilder('l')
            ->select('l') 
            ->where('l.valide = :valide')
            ->andWhere('l.coderep = :coderep') 
            ->setParameter('valide', 0)
            ->setParameter('coderep', $coderep) 
            ->orderBy('l.nummvt') 
            
            ->getQuery()
            ->getResult();
    }
    
    public function getLigneWebByCodeTrs(String $codetrs): array
    {
        return $this->createQueryBuilder('l')
            ->select('l') 
            ->andWhere('l.codetrs = :codetrs') 
            ->setParameter('codetrs', $codetrs) 
            ->orderBy('l.nummvt') 
            ->getQuery()
            ->getResult();
    }
    public function getLigneWebByCodeRep(String $coderep): array
    {
        return $this->createQueryBuilder('l')
            ->select('l') 
            ->andWhere('l.coderep = :coderep') 
            ->setParameter('coderep', $coderep) 
            ->orderBy('l.nummvt') 
            ->getQuery()
            ->getResult();
    }
    
    
    

    //    /**
    //     * @return LigneWeb[] Returns an array of LigneWeb objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?LigneWeb
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
