<?php

// src/Service/PricingService.php
namespace App\Service;

use App\Entity\Dynamic\Fourchette;

class PricingService
{

    private $dynamicEntityManagerService;
    public function __construct(
        DynamicEntityManagerService $dynamicEntityManagerService,
    ) {
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
    }

    public function calculateDiscount(string $codeArt, float $qteArt, string $tarif): float
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $fourchetteRepository = $entityManager->getRepository(Fourchette::class);

        $remFourch = $fourchetteRepository->createQueryBuilder('f')
            ->andWhere('f.codeart = :codeart')
            ->andWhere('f.ctarif = :tarif')
            ->andWhere('f.qtemin < :qte')
            ->andWhere('f.qtemax >= :qte')
            ->setParameter('codeart', $codeArt)
            ->setParameter('tarif', $tarif)
            ->setParameter('qte', $qteArt)
            ->orderBy('f.qtemin', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($remFourch !== null) {
            $newDiscount = $remFourch->getAjustement();
            
            if (round($newDiscount, 2) > round($existingDiscount, 2)) {
                return (float) $newDiscount;
            }
        }

        return $existingDiscount;
    }

}
