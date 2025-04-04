<?php

// src/Service/ClientReportService.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ClientReportService
{
    private $em;
    private $dynamicEntityManagerService;
   
         public function __construct(DynamicEntityManagerService $dynamicEntityManagerService)
    {
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
    }

    public function generateReport(array $params): array
    {
        // Récupérer les paramètres
        $tcc = $params['tcc'];
        $tcc1 = $params['tcc1'];
        $td1 = $params['td1'];
        $td2 = $params['td2'];
        $codeUser = $params['tcc'];
        $societe = $params['societe'];
 
        // Supprimer les données temporaires
        $this->deleteTempData($codeUser, $societe);

        // Construire les requêtes SQL
        $requete = "codetrs >= :tcc AND codetrs <= :tcc1 AND datemvt >= :td1 AND datemvt <= :td2";
        $reqMvt = "e.codetrs = :tcc AND e.datemvt >= :td1 AND e.datemvt <= :td2";

        // Exécuter les requêtes
        $mouvements = $this->getMouvements($requete, [
            'tcc' => $tcc,
            'tcc1' => $tcc1,
            'td1' => $td1,
            'td2' => $td2,
        ]);

        // Générer les rapports
        $ficheClient = $this->ficheClient($td1, $td2, $requete, $societe);
        $ficheClientDet = $this->ficheClientDet($td1, $td2, $reqMvt, $societe);

        // Mettre à jour les soldes
        $this->updateSoldes($tcc, $td1, $codeUser);

        // Retourner les résultats
        return [
            'mouvements' => $mouvements,
            'ficheClient' => $ficheClient,
            'ficheClientDet' => $ficheClientDet,
        ];
    }

    private function deleteTempData(string $codeUser, string $societe): void
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $connection = $entityManager->getConnection();
        $connection->executeStatement("DELETE FROM tmpmvtcaisse WHERE usera = :usera", ['usera' => $codeUser]);
        $connection->executeStatement("DELETE FROM baseimpr.lmvt WHERE codeuser = :codeuser AND societe = :societe", [
            'codeuser' => $codeUser,
            'societe' => $societe,
        ]);
    }

    private function getMouvements(string $requete, array $params): array
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $connection = $entityManager->getConnection();
    
        $sql = "SELECT * FROM lmvt WHERE $requete";
        $result = $connection->executeQuery($sql, $params);
    
        // Utiliser fetchAllAssociative pour récupérer les résultats
        return $result->fetchAllAssociative();
    }

    private function ficheClient(string $td1, string $td2, string $requete, string $societe): array
    {
        // Implémentez la logique de Fiche_Client ici
        return [];
    }

    private function ficheClientDet(string $td1, string $td2, string $reqMvt, string $societe): array
    {
        // Implémentez la logique de Fiche_Client_Det ici
        return [];
    }

    private function updateSoldes(string $tcc, string $td1, string $codeUser): void
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $connection = $entityManager->getConnection();
        // Insérer les soldes initiaux
        $sql = "INSERT INTO tmpmvtcaisse (nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera)
                SELECT 'S.I', 'S.I', :dateReport, 'Report Au : ' || :dateReport, 0, '000000000000000', code, libelle, '', 0, :usera
                FROM client WHERE code = :tcc ";
        $connection->executeStatement($sql, [
            'dateReport' => (new \DateTime($td1))->modify('-1 day')->format('Y-m-d'),
            'usera' => $codeUser,
            'tcc' => $tcc,
         ]);

        // Mettre à jour les soldes
        $sql = "UPDATE tmpmvtcaisse SET solde = :solde, tel = :tel, fax = :fax, gsm = :gsm, adresse = :adresse, codetva = :codetva
                WHERE codetrs = :codetrs AND usera = :usera";
        // Exécutez cette requête pour chaque client
    }
}