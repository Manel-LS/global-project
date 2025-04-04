<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeInterface;

class MtechuClientService
{
    private $dynamicEntityManagerService;

    public function __construct(DynamicEntityManagerService $dynamicEntityManagerService)
    {
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
    }
   
  
    public function mtechuCli($echeance, $codeTrs, $codeSoc, $baseR, $connection, $PARAM): float
    {
         $MTEchu_Cli = 0.0;
         $echeanceFormatted = $echeance->format('Y-m-d');
         if ($baseR == "S") {
            $baseR = "S";
        } else {
            $baseR = "B";
        }
    
        // Requête principale
        if ($PARAM->getParamEchu() == "B") {
            $sql = "SELECT SUM(montant) AS MTEchu 
                    FROM reglement 
                    WHERE echeance <= :echeance 
                      AND (numbord = '' OR numbord IS NULL) 
                      AND endosser = 0 
                      AND impayer = 0 
                      AND modifregl = 0 
                      AND impcaisse = 0 
                      AND garantie = 0 
                      AND baseregl = :baseR 
                      AND (typeregl = 'Chèque' OR typeregl = 'Effet') 
                      AND codetrs = :codeTrs 
                    GROUP BY codetrs 
                    LIMIT 500";
        } else {
            $sql = "SELECT SUM(montant) AS MTEchu 
                    FROM reglement 
                    WHERE echeance <= :echeance 
                      AND encaisser = 0 
                      AND impayer = 0 
                      AND impcaisse = 0 
                      AND modifregl = 0 
                      AND endosser = 0 
                      AND garantie = 0 
                      AND baseregl = :baseR 
                      AND (typeregl = 'Chèque' OR typeregl = 'Effet') 
                      AND codetrs = :codeTrs 
                    GROUP BY codetrs 
                    LIMIT 500";
        }
    
        $params = [
            'echeance' => $echeanceFormatted,
            'baseR' => $baseR,
            'codeTrs' => $codeTrs,
        ];
    
        $res = $connection->executeQuery($sql, $params);
        $result = $res->fetchAssociative();
    
        if ($result && !is_null($result['MTEchu'])) {
            $MTEchu_Cli += (float)$result['MTEchu'];
        }
    
        $sqlExercice = "SELECT * FROM exercice ORDER BY anne";
        $resultExercice = $connection->executeQuery($sqlExercice);
        while ($rowExercice = $resultExercice->fetchAssociative()) {
             $codeSoc = $rowExercice['codesoc'];
    
            if ($PARAM->getParamEchu() == "B") {
                $sql = "SELECT SUM(montant) AS MTEchu 
                        FROM {$codeSoc}.reglement 
                        WHERE echeance <= :echeance 
                          AND (numbord = '' OR numbord IS NULL) 
                          AND endosser = 0 
                          AND impayer = 0 
                          AND modifregl = 0 
                          AND impcaisse = 0 
                          AND garantie = 0 
                          AND baseregl = :baseR 
                          AND (typeregl = 'Chèque' OR typeregl = 'Effet') 
                          AND codetrs = :codeTrs 
                        GROUP BY codetrs 
                        LIMIT 500";
            } else {
                $sql = "SELECT SUM(montant) AS MTEchu 
                        FROM {$codeSoc}.reglement 
                        WHERE echeance <= :echeance 
                          AND encaisser = 0 
                          AND impayer = 0 
                          AND impcaisse = 0 
                          AND modifregl = 0 
                          AND endosser = 0 
                          AND garantie = 0 
                          AND baseregl = :baseR 
                          AND (typeregl = 'Chèque' OR typeregl = 'Effet') 
                          AND codetrs = :codeTrs 
                        GROUP BY codetrs 
                        LIMIT 500";
            }
    
            $params = [
                'echeance' => $echeanceFormatted,
                'baseR' => $baseR,
                'codeTrs' => $codeTrs,
            ];
    
            $res = $connection->executeQuery($sql, $params);
            $result = $res->fetchAssociative();
    
            if ($result && !is_null($result['MTEchu'])) {
                $MTEchu_Cli += (float)$result['MTEchu'];
            }
        }
    
        return $MTEchu_Cli;
    }
    
    
    public function MTEncourCli($echeance, $codeTrs, $baseR,  $connection, $param): float
    {
        $mtEncourCli = 0.0;
        $echeanceFormatted = $echeance->format('Y-m-d');
        $baseR = ($baseR == "S") ? "S" : "B";
        
        $sql = "SELECT SUM(montant) AS MTEncour
                FROM reglement
                WHERE echeance > :echeance
                  AND (numbord = '' OR numbord IS NULL)
                  AND impayer = 0
                  AND modifregl = 0
                  AND impcaisse = 0
                  AND garantie = 0
                  AND baseregl = :baseR
                  AND (typeregl = 'Chèque' OR typeregl = 'Effet')
                  AND codetrs = :codeTrs
                GROUP BY codetrs
                LIMIT 500";
        
        $params = [
            'echeance' => $echeanceFormatted,
            'baseR' => $baseR,
            'codeTrs' => $codeTrs,
        ];
        
        $res = $connection->executeQuery($sql, $params);
        $result = $res->fetchAssociative();
        
         if ($result && !is_null($result['MTEncour'])) {
            $mtEncourCli = (float)number_format((float)$result['MTEncour'], 3, '.', '');
        }
    
         $sqlExercice = "SELECT * FROM exercice ORDER BY anne";
        $resultExercice = $connection->executeQuery($sqlExercice);
        
        while ($rowExercice = $resultExercice->fetchAssociative()) {
            $codeSoc = $rowExercice['codesoc'];
    
             $sqlExerciceSpecific = "SELECT SUM(montant) AS MTEncour
                                    FROM $codeSoc.reglement
                                    WHERE echeance > :echeance
                                      AND (numbord = '' OR numbord IS NULL)
                                      AND impayer = 0
                                      AND modifregl = 0
                                      AND impcaisse = 0
                                      AND garantie = 0
                                      AND baseregl = :baseR
                                      AND (typeregl = 'Chèque' OR typeregl = 'Effet')
                                      AND codetrs = :codeTrs
                                    GROUP BY codetrs
                                    LIMIT 500";
            
            $paramsExercice = [
                'echeance' => $echeanceFormatted,
                'baseR' => $baseR,
                'codeTrs' => $codeTrs,
            ];
    
            $resExercice = $connection->executeQuery($sqlExerciceSpecific, $paramsExercice);
            $resultExerciceSpecific = $resExercice->fetchAssociative();
            
             if ($resultExerciceSpecific && !is_null($resultExerciceSpecific['MTEncour'])) {
                $mtEncourCli += (float)number_format((float)$resultExerciceSpecific['MTEncour'], 3, '.', '');
            }
        }
    
        return $mtEncourCli;
    }
    
 
    public function MTSoldeImpayer($codeTrs, $connection): float
    {
         $mtSoldeImpayer = 0.0;
    
         $sql = "SELECT SUM(montant + fraisimp - mtrapp) AS MTImp
                FROM impclient
                WHERE codetrs = :codeTrs
                GROUP BY codetrs
                LIMIT 500";
    
         $params = ['codeTrs' => $codeTrs];
    
        // Exécution de la requête
        $res = $connection->executeQuery($sql, $params);
        $result = $res->fetchAssociative();
    
         if ($result && !is_null($result['MTImp'])) {
            $mtSoldeImpayer = (float)number_format((float)$result['MTImp'], 3, '.', '');
        }
    
         $sqlExercice = "SELECT * FROM exercice ORDER BY anne";
        $resultExercice = $connection->executeQuery($sqlExercice);
    
         while ($rowExercice = $resultExercice->fetchAssociative()) {
            $codeSoc = $rowExercice['codesoc'];
    
             $sqlExerciceSpecific = "SELECT SUM(montant + fraisimp - mtrapp) AS MTImp
                                    FROM $codeSoc.impclient
                                    WHERE codetrs = :codeTrs
                                    GROUP BY codetrs
                                    LIMIT 500";
    
             $paramsExercice = ['codeTrs' => $codeTrs];
    
             $resExercice = $connection->executeQuery($sqlExerciceSpecific, $paramsExercice);
            $resultExerciceSpecific = $resExercice->fetchAssociative();
    
             if ($resultExerciceSpecific && !is_null($resultExerciceSpecific['MTImp'])) {
                $mtSoldeImpayer += (float)number_format((float)$resultExerciceSpecific['MTImp'], 3, '.', '');
            }
        }
    
        return $mtSoldeImpayer;
    }
    
    public function MTChqGarClient($codeTrs, $connection): float
    {
         $mtChequeGarClient = 0.0;
    
         $sql = "SELECT SUM(montant) AS MTCheque_Cli
                FROM reglement
                WHERE (typeregl = 'Chèque' OR typeregl = 'Effet')
                AND codetrs = :codeTrs
                AND garantie = 1
                AND encaisser = 0
                AND impayer = 0
                GROUP BY codetrs
                LIMIT 1000";
    
         $params = ['codeTrs' => $codeTrs];
    
         $res = $connection->executeQuery($sql, $params);
        $result = $res->fetchAssociative();
    
         if ($result && !is_null($result['MTCheque_Cli'])) {
            $mtChequeGarClient = (float)number_format((float)$result['MTCheque_Cli'], 3, '.', '');
        }
    
        return $mtChequeGarClient;
    }
    
    public function livEncours( $codeTrs,  $nomTable,  $dateMvt,  $connection): float
    {
         $livEncours = 0.0;
    
         $req = "";
        if ($dateMvt !== null && $dateMvt !== "") {
            $req = " AND datemvt <= :dateMvt";
        }
    
        $sql = "SELECT SUM(mttc) AS soldeliv 
                FROM $nomTable 
                WHERE codefact = 'N' AND codetrs = :codeTrs $req";
    
        // Exécution de la requête
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('codeTrs', $codeTrs);
    
        if ($dateMvt !== null && $dateMvt !== "") {
            $stmt->bindValue('dateMvt', $dateMvt);
        }
    
        $result = $stmt->executeQuery();
    
        $row = $result->fetchAssociative();
        if ($row !== false && isset($row['soldeliv'])) {
            $livEncours = (float) $row['soldeliv'];
        }
    
        return round($livEncours, 3);
    }
}