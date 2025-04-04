<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class FicheClientService
{
    private $dynamicEntityManagerService;

    private RequestStack $requestStack;
    public function __construct(DynamicEntityManagerService $dynamicEntityManagerService, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
    }

    public function ficheClient($codetrs, $startDate, $endDate, $requete, $baseF, $chkdebour, $societe, $typeFiche, Connection $connection, $PARAM, $codeUser): JsonResponse
    {

        $reqRegl = "";
        $champVentReg = "";
        $session = $this->requestStack->getSession();
        $codeSoc = $session->get('database_choice');

        if ($baseF === "E" || $baseF === "C") {
            $reqRegl = " AND baseregl = 'B' AND garantie='0' ";
        } elseif ($baseF === "F") {
            $reqRegl = " AND (baseregl = 'B' OR baseregl='F') AND garantie='0' ";
        } elseif ($baseF === "S") {
            $reqRegl = " AND baseregl = 'S' AND garantie='0' ";
        } elseif ($baseF === "G") {
            $reqRegl = " AND (baseregl = 'B' OR baseregl='S') AND garantie='0' ";
        }

        if ($PARAM->getTypeRapp() === "BL") {
            $champVentReg = "restevbl";
        } else {
            $champVentReg = "resteventiler";
        }

        $ses = "nommvt, num, datemvt, libelle, echeance, montant1, temps, codetrs, libtrs, ncompte, solde1, libcaisse, usera";

        if ($typeFiche === "Date") {
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'RG', nummvt, datemvt, CONCAT('RG N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque, ' / ', libcaisse), 
                   echeance, montant, temps, codetrs, libtrs, rapprocher, ROUND($champVentReg, 3), banquecli, :codeUser 
            FROM $codeSoc.reglement 
            WHERE fichecli='1' AND $requete $reqRegl AND datemvt BETWEEN :startDate AND :endDate";

            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);
        } elseif ($typeFiche === "Ech") {
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'RG', nummvt, datemvt, CONCAT('RG N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque, ' / ', libcaisse), 
                           echeance, montant, temps, codetrs, libtrs, rapprocher, ROUND($champVentReg, 3), banquecli, :codeUser 
                    FROM $codeSoc.reglement 
                    WHERE fichecli='1' AND (typeregl <> 'Chèque' AND typeregl <> 'Effet') AND $requete $reqRegl";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);

            $requeteEch = str_replace("datemvt", "echeance", $requete);
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'RG', nummvt, datemvt, CONCAT('RG N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque, ' / ', libcaisse), 
                           echeance, montant, temps, codetrs, libtrs, rapprocher, ROUND($champVentReg, 3), banquecli, :codeUser 
                    FROM $codeSoc.reglement 
                    WHERE fichecli='1' AND (typeregl = 'Chèque' OR typeregl = 'Effet') AND $requeteEch $reqRegl";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);
        } elseif ($typeFiche === "Enc") {
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'RG', nummvt, datemvt, CONCAT('RG N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque, ' / ', libcaisse), 
                           echeance, montant, temps, codetrs, libtrs, rapprocher, ROUND($champVentReg, 3), banquecli, :codeUser 
                    FROM $codeSoc.reglement 
                    WHERE fichecli='1' AND (typeregl <> 'Chèque' AND typeregl <> 'Effet') AND $requete $reqRegl";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);

            $requeteEnc = str_replace("datemvt", "dateenc", $requete);
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'RG', nummvt, datemvt, CONCAT('RG N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque, ' / ', libcaisse), 
                           echeance, montant, temps, codetrs, libtrs, rapprocher, ROUND($champVentReg, 3), banquecli, :codeUser 
                    FROM $codeSoc.reglement 
                    WHERE fichecli='1' AND (typeregl = 'Chèque' OR typeregl = 'Effet') AND $requeteEnc $reqRegl";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);
        }

        // Insertion des débours clients
        // if ($debours === "1") {
        //     $ses = "nommvt, num, datemvt, libelle, echeance, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
        //     $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
        //             SELECT 'DE', nummvt, datemvt, CONCAT('DE N° : ', nummvt, ' / Debours / MT : ', montant), 
        //                    :dateZero, (resteventiler) AS mtdeb, temps, codetrs, libtrs, rapprocher, ROUND(resteventiler, 3), :codeUser 
        //             FROM $codeSoc.regldeb 
        //             WHERE ROUND(resteventiler, 3) > 0 AND $requete";
        //     $connection->executeStatement($sql, ['dateZero' => '0000-00-00', 'codeUser' => $codeUser]);
        // }

        // Insertion des impayés et autres mouvements
        if ($baseF !== "S") {
            // Insertion des impayés banque
            $ses = "nommvt, num, datemvt, libelle, echeance, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'IM', nummvt, datemvt, CONCAT('IM N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque), 
                           echeance, ROUND(montant + fraisimp, 3), temps, codetrs, libtrs, '', ROUND(montant + fraisimp - mtrapp, 3), :codeUser 
                    FROM $codeSoc.impclient 
                    WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl <> 'S') AND $requete";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate
            ]);

            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'IC', nummvt, datemvt, CONCAT('IC N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque), 
                           echeance, montant, temps, codetrs, libtrs, '', ROUND(montant + fraisimp - mtrapp, 3), :codeUser 
                    FROM $codeSoc.impcaisse 
                    WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl <> 'S') AND $requete";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate
            ]);
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'MR', nummvt, datemvt, CONCAT('MR N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque), 
                           echeance, montant, temps, codetrs, libtrs, '', ROUND(montant + fraisimp - mtrapp, 3), :codeUser 
                    FROM $codeSoc.modifregl 
                    WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl <> 'S') AND $requete";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate
            ]);
            if ($baseF === "G") {
                goto AvecBaseG;
            }
        } else {
            AvecBaseG:
            $ses = "nommvt, num, datemvt, libelle, echeance, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'IM', nummvt, datemvt, CONCAT('IM N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque), 
                           echeance, ROUND(montant + fraisimp, 3), temps, codetrs, libtrs, '', ROUND(montant + fraisimp - mtrapp, 3), :codeUser 
                    FROM $codeSoc.impclient 
                    WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl = 'S') AND $requete";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,
                'endDate'   => $endDate
            ]);
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'IC', nummvt, datemvt, CONCAT('IC N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque), 
                           echeance, montant, temps, codetrs, libtrs, '', ROUND(montant + fraisimp - mtrapp, 3), :codeUser 
                    FROM $codeSoc.impcaisse 
                    WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl = 'S') AND $requete";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,  // Bind startDate
                'endDate'   => $endDate     // Bind endDate
            ]);
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'MR', nummvt, datemvt, CONCAT('MR N° : ', nummvt, ' / ', LEFT(typeregl, 2), ' / ', numcheque), 
                           echeance, montant, temps, codetrs, libtrs, '', ROUND(montant + fraisimp - mtrapp, 3), :codeUser 
                    FROM $codeSoc.modifregl 
                    WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl = 'S') AND $requete";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,  // Bind startDate
                'endDate'   => $endDate     // Bind endDate
            ]);
        }

        $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
        $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                SELECT 'FC', nummvt, datemvt, CONCAT('FC N° : ', nummvt), (mttc + timbref) AS soldef, temps, codetrs, libtrs, '', ROUND(mttc + timbref - mtrapp, 3), :codeUser 
                FROM $codeSoc.efc 
                WHERE codefact <> 'A' AND $requete";
        $connection->executeStatement($sql, [
            'codeUser' => $codeUser,
            'codetrs'  => $codetrs,
            'startDate' => $startDate,  // Bind startDate
            'endDate'   => $endDate     // Bind endDate
        ]);
        if ($baseF === "F") {
            $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'FA', nummvt, datemvt, CONCAT('FA N° : ', nummvt), (mttc + timbref) AS soldef, temps, codetrs, libtrs, '', ROUND(mttc + timbref - mtrapp, 3), :codeUser 
                    FROM $codeSoc.efactv 
                    WHERE typefact = 'FA' AND codefact <> 'A' AND $requete";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,  // Bind startDate
                'endDate'   => $endDate     // Bind endDate
            ]);
            $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                    SELECT 'AV', nummvt, datemvt, CONCAT('AV N° : ', nummvt), (mttc + timbref) AS soldef, temps, codetrs, libtrs, '', ROUND(mttc + timbref - mtrapp, 3), :codeUser 
                    FROM $codeSoc.efactv 
                    WHERE typefact = 'AV' AND codefact <> 'A' AND $requete";
            $connection->executeStatement($sql, [
                'codeUser' => $codeUser,
                'codetrs'  => $codetrs,
                'startDate' => $startDate,  // Bind startDate
                'endDate'   => $endDate     // Bind endDate
            ]);
            if ($PARAM->getCalcLivCour() == "0") {
                $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                        SELECT 'BL', nummvt, datemvt, CONCAT('BL N° : ', nummvt, ' / Fact : ', numfact), mttc, temps, codetrs, libtrs, '', ROUND(mttc - mtrapp, 3), :codeUser 
                        FROM $codeSoc.ebl 
                        WHERE codefact = 'N' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,  // Bind startDate
                    'endDate'   => $endDate     // Bind endDate
                ]);
                $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                        SELECT 'BR', nummvt, datemvt, CONCAT('BR N° : ', nummvt, ' / Fact : ', numfact), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
                        FROM $codeSoc.ebrc 
                        WHERE codefact = 'N' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,  // Bind startDate
                    'endDate'   => $endDate     // Bind endDate
                ]);
            } elseif ($baseF === "E") {
                // Logique spécifique pour la base E
                if ($PARAM->getGestionBS() === "0") {
                    $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                    $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                SELECT 'BS', nummvt, datemvt, CONCAT('BS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', ROUND(mttc - mtrapp, 3), :codeUser 
                FROM $codeSoc.ebs 
                WHERE codefact <> 'A' AND $requete";
                    $connection->executeStatement($sql, [
                        'codeUser' => $codeUser,
                        'codetrs'  => $codetrs,
                        'startDate' => $startDate,
                        'endDate'   => $endDate
                    ]);

                    $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                    $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                SELECT 'RS', nummvt, datemvt, CONCAT('RS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
                FROM $codeSoc.ebrs 
                WHERE codefact <> 'A' AND $requete";
                    $connection->executeStatement($sql, [
                        'codeUser' => $codeUser,
                        'codetrs'  => $codetrs,
                        'startDate' => $startDate,
                        'endDate'   => $endDate
                    ]);
                }

                if ($PARAM->getTypeRapp() === "BL") {
                    $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                    $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                SELECT 'TI', nummvt, datemvt, CONCAT('FA N° : ', nummvt), timbref, temps, codetrs, libtrs, '', ROUND(timbref - mtrapp, 3), :codeUser 
                FROM $codeSoc.efactv 
                WHERE codefact <> 'A' AND typefact='FA' AND $requete";
                    $connection->executeStatement($sql, [
                        'codeUser' => $codeUser,
                        'codetrs'  => $codetrs,
                        'startDate' => $startDate,
                        'endDate'   => $endDate
                    ]);
                }

                $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'AV', nummvt, datemvt, CONCAT('AV N° : ', nummvt), (mttc + timbref) AS soldef, temps, codetrs, libtrs, '', ROUND(mttc + timbref - mtrapp, 3), :codeUser 
            FROM $codeSoc.efactv 
            WHERE typefact = 'AV' AND codefact <> 'A' AND numbc='AV_FIN' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'BL', nummvt, datemvt, CONCAT('BL N° : ', nummvt, ' / Fact : ', numfact), mttc, temps, codetrs, libtrs, '', ROUND(mttc - mtrapp, 3), :codeUser 
            FROM $codeSoc.ebl 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'TC', nummvt, datemvt, CONCAT('TC N° : ', nummvt, ' / Fact : ', numfact), mttcnet, temps, codetrs, libtrs, '', 0, :codeUser 
            FROM $codeSoc.etick 
            WHERE (codefact = 'N' OR codefact = 'O' OR codefact IS NULL) AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                // Gestion des caisses CV
                $caisses = $connection->fetchAllAssociative("SELECT * FROM $codeSoc.caisse WHERE typecaisse = 'CV' ORDER BY code");
                foreach ($caisses as $caisse) {
                    $codeCaisse = $caisse['code'];
                    $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                SELECT 'TC', nummvt, datemvt, CONCAT('TC N° : ', nummvt, ' / Fact : ', numfact), mttcnet, temps, codetrs, libtrs, '', 0, :codeUser 
                FROM $codeSoc.etick$codeCaisse 
                WHERE (codefact = 'N' OR codefact = 'O' OR codefact IS NULL) AND $requete";
                    $connection->executeStatement($sql, [
                        'codeUser' => $codeUser,
                        'codetrs'  => $codetrs,
                        'startDate' => $startDate,
                        'endDate'   => $endDate
                    ]);
                }

                $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'BR', nummvt, datemvt, CONCAT('BR N° : ', nummvt, ' / Fact : ', numfact), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
            FROM $codeSoc.ebrc 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);
            } elseif ($baseF === "G") {
                // Logique spécifique pour la base G
                if ($PARAM->getGestionProjet() === "1") {
                    $listemvts = $connection->fetchAllAssociative("SELECT * FROM $codeSoc.listemvt WHERE chantier = '1' AND fichecli='1' ORDER BY code");
                    foreach ($listemvts as $listemvt) {
                        $codeMvt = $listemvt['code'];
                        if ($listemvt['stock'] === "S") {
                            $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                        SELECT 'BS', nummvt, datemvt, CONCAT('BS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
                        FROM $codeSoc.emvt 
                        WHERE codefact <> 'A' AND LEFT(nummvt, 2) = :codeMvt AND $requete";
                            $connection->executeStatement($sql, [
                                'codeUser' => $codeUser,
                                'codeMvt'  => $codeMvt,
                                'startDate' => $startDate,
                                'endDate'   => $endDate
                            ]);
                        } elseif ($listemvt['stock'] === "E") {
                            $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                            $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                        SELECT 'BR', nummvt, datemvt, CONCAT('BR N° : ', nummvt), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
                        FROM $codeSoc.emvt 
                        WHERE codefact <> 'A' AND LEFT(nummvt, 2) = :codeMvt AND $requete";
                            $connection->executeStatement($sql, [
                                'codeUser' => $codeUser,
                                'codeMvt'  => $codeMvt,
                                'startDate' => $startDate,
                                'endDate'   => $endDate
                            ]);
                        }
                    }
                }

                $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'AV', nummvt, datemvt, CONCAT('AV N° : ', nummvt), (mttc + timbref) AS soldef, temps, codetrs, libtrs, '', ROUND(mttc + timbref - mtrapp, 3), :codeUser 
            FROM $codeSoc.efactv 
            WHERE typefact = 'AV' AND codefact <> 'A' AND numbc='AV_FIN' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'BS', nummvt, datemvt, CONCAT('BS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', ROUND(mttc - mtrapp, 3), :codeUser 
            FROM $codeSoc.ebs 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'RS', nummvt, datemvt, CONCAT('RS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
            FROM $codeSoc.ebrs 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'BL', nummvt, datemvt, CONCAT('BL N° : ', nummvt, ' / Fact : ', numfact), mttc, temps, codetrs, libtrs, '', ROUND(mttc - mtrapp, 3), :codeUser 
            FROM $codeSoc.ebl 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'TC', nummvt, datemvt, CONCAT('TC N° : ', nummvt, ' / Fact : ', numfact), mttcnet, temps, codetrs, libtrs, '', 0, :codeUser 
            FROM $codeSoc.etick 
            WHERE (codefact = 'N' OR codefact = 'O' OR codefact IS NULL) AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                // Gestion des caisses CV
                $caisses = $connection->fetchAllAssociative("SELECT * FROM $codeSoc.caisse WHERE typecaisse = 'CV' ORDER BY code");
                foreach ($caisses as $caisse) {
                    $codeCaisse = $caisse['code'];
                    $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
                SELECT 'TC', nummvt, datemvt, CONCAT('TC N° : ', nummvt, ' / Fact : ', numfact), mttcnet, temps, codetrs, libtrs, '', 0, :codeUser 
                FROM $codeSoc.etick$codeCaisse 
                WHERE (codefact = 'N' OR codefact = 'O' OR codefact IS NULL) AND $requete";
                    $connection->executeStatement($sql, [
                        'codeUser' => $codeUser,
                        'codetrs'  => $codetrs,
                        'startDate' => $startDate,
                        'endDate'   => $endDate
                    ]);
                }

                $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'BR', nummvt, datemvt, CONCAT('BR N° : ', nummvt, ' / Fact : ', numfact), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
            FROM $codeSoc.ebrc 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);
            } elseif ($baseF === "S") {
                // Logique spécifique pour la base S
                $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'BS', nummvt, datemvt, CONCAT('BS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', ROUND(mttc - mtrapp, 3), :codeUser 
            FROM $codeSoc.ebs 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);

                $ses = "nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'RS', nummvt, datemvt, CONCAT('RS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
            FROM $codeSoc.ebrs 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);
            } elseif ($baseF === "C") {
                // Logique spécifique pour la base C
                $ses = "nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera";
                $sql = "INSERT INTO baseimpr.tmpmvtcaisse ($ses) 
            SELECT 'BC', nummvt, datemvt, CONCAT('BC N° : ', nummvt), mttc, temps, codetrs, libtrs, '', ROUND(mttc, 3), :codeUser 
            FROM $codeSoc.ebcc 
            WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, [
                    'codeUser' => $codeUser,
                    'codetrs'  => $codetrs,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]);
            }
        }
        return new JsonResponse(['status' => 'success', 'message' => 'Fiche client générée avec succès.']);
    }



    public function ficheClientDet($codetrs, $startDate, $endDate, $requete, $baseF, $debours, $societe, $typeFiche, $connection, $codeUser, $PARAM): JsonResponse
    {
        $reqRegl = "";
        $session = $this->requestStack->getSession();
        $codeSoc = $session->get('database_choice');

        if ($baseF === "E") {
            $reqRegl = " AND baseregl = 'B'";
        } elseif ($baseF === "F") {
            $reqRegl = " AND (baseregl = 'B' OR baseregl='F')";
        } elseif ($baseF === "S") {
            $reqRegl = " AND baseregl = 'S'";
        } elseif ($baseF === "G") {
            $reqRegl = " AND (baseregl = 'B' OR baseregl='S')";
        }

        $ses = "pieceliee, nummvt, codetrs, codeuser, societe";

        // Paramètres communs à toutes les requêtes
        $commonParams = [
            'codeUser' => $codeUser,
            'codetrs' => $codetrs,
            'societe' => $societe,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        if ($typeFiche === "Date") {
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                    FROM $codeSoc.reglement AS e 
                    WHERE fichecli='1' AND garantie = 0 AND $requete $reqRegl";
            $connection->executeStatement($sql, $commonParams);
        } elseif ($typeFiche === "Ech") {
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                    FROM $codeSoc.reglement AS e 
                    WHERE fichecli='1' AND garantie = 0 AND (typeregl <> 'Chèque' AND typeregl <> 'Effet') AND $requete $reqRegl";
            $connection->executeStatement($sql, $commonParams);

            $requeteEch = str_replace("datemvt", "echeance", $requete);
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                    FROM $codeSoc.reglement AS e 
                    WHERE fichecli='1' AND garantie = 0 AND (typeregl = 'Chèque' OR typeregl = 'Effet') AND $requeteEch $reqRegl";
            $connection->executeStatement($sql, $commonParams);
        } elseif ($typeFiche === "Enc") {
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                    FROM $codeSoc.reglement AS e 
                    WHERE fichecli='1' AND garantie = 0 AND (typeregl <> 'Chèque' AND typeregl <> 'Effet') AND $requete $reqRegl";
            $connection->executeStatement($sql, $commonParams);

            $requeteEnc = str_replace("datemvt", "dateenc", $requete);
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                    FROM $codeSoc.reglement AS e 
                    WHERE fichecli='1' AND garantie = 0 AND (typeregl = 'Chèque' OR typeregl = 'Effet') AND $requeteEnc $reqRegl";
            $connection->executeStatement($sql, $commonParams);
        }

        if ($debours === "1") {
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                    FROM $codeSoc.regldeb AS e 
                    WHERE ROUND(resteventiler, 3) > 0 AND $requete";
            $connection->executeStatement($sql, $commonParams);
        }

        $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                FROM $codeSoc.impclient AS e 
                WHERE 1=1 AND $requete";
        $connection->executeStatement($sql, $commonParams);

        $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                FROM $codeSoc.impcaisse AS e 
                WHERE 1=1 AND $requete";
        $connection->executeStatement($sql, $commonParams);

        $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                SELECT 'TIND', nummvt, :codetrs, :codeUser, :societe 
                FROM $codeSoc.modifregl AS e 
                WHERE 1=1 AND $requete";
        $connection->executeStatement($sql, $commonParams);

        $ses = "pieceliee, nummvt, datemvt, codetrs, codeart, desart, qteart, puttc, mttotal, temps, codeuser, societe";
        $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                SELECT 'FC', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                FROM $codeSoc.efc AS e, $codeSoc.lfc AS l 
                WHERE e.nummvt = l.nummvt AND $requete";
        $connection->executeStatement($sql, $commonParams);

        if ($baseF === "F") {
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'FA', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                    FROM $codeSoc.efactv AS e, $codeSoc.lfactv AS l 
                    WHERE e.typefact = 'FA' AND e.nummvt = l.nummvt AND $requete";
            $connection->executeStatement($sql, $commonParams);

            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'AV', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                    FROM $codeSoc.efactv AS e, $codeSoc.lfactv AS l 
                    WHERE e.typefact = 'AV' AND e.nummvt = l.nummvt AND $requete";
            $connection->executeStatement($sql, $commonParams);
        } elseif ($baseF === "E") {
            $gestionBS = $PARAM->getGestionbs();

            if ($gestionBS === "0") {
                $sql = "INSERT INTO baseimpr.lmvt (pieceliee, nummvt, codetrs, datemvt, codeart, desart, qteart, puttc, mttotal, temps, codeuser, societe) 
                        SELECT 'BS', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                        FROM $codeSoc.ebs AS e, $codeSoc.lbs AS l 
                        WHERE e.nummvt = l.nummvt AND $requete";
                $connection->executeStatement($sql, $commonParams);

                $sql = "INSERT INTO tmpmvtcaisse (nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera) 
                        SELECT 'RS', nummvt, datemvt, CONCAT('RS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
                        FROM $codeSoc.ebrs AS e 
                        WHERE codefact <> 'A' AND $requete";
                $connection->executeStatement($sql, ['codeUser' => $codeUser] + $commonParams);
            }

            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'BL', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                    FROM $codeSoc.ebl AS e, $codeSoc.lbl AS l 
                    WHERE e.nummvt = l.nummvt AND $requete";
            $connection->executeStatement($sql, $commonParams);

            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'BR', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                    FROM $codeSoc.ebrc AS e, $codeSoc.lbrc AS l 
                    WHERE e.nummvt = l.nummvt AND $requete";
            $connection->executeStatement($sql, $commonParams);

            // Tickets
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'TC', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                    FROM $codeSoc.etick AS e, $codeSoc.ltick AS l 
                    WHERE e.nummvt = l.nummvt AND $requete";
            $connection->executeStatement($sql, $commonParams);

            // Tickets par caisse
            $caisses = $connection->fetchAllAssociative("SELECT code FROM $codeSoc.caisse WHERE typecaisse = 'CV' ORDER BY code");
            foreach ($caisses as $caisse) {
                $codeCaisse = $caisse['code'];
                $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                        SELECT 'TC', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                        FROM $codeSoc.etick$codeCaisse AS e, $codeSoc.ltick$codeCaisse AS l 
                        WHERE e.nummvt = l.nummvt AND $requete";
                $connection->executeStatement($sql, $commonParams);
            }
        } elseif ($baseF === "S") {
            $sql = "INSERT INTO baseimpr.lmvt ($ses) 
                    SELECT 'BS', l.nummvt, l.datemvt, e.codetrs, l.codeart, l.desart, l.qteart, l.puttc, l.mttotal, e.temps, :codeUser, :societe 
                    FROM $codeSoc.ebs AS e, $codeSoc.lbs AS l 
                    WHERE e.nummvt = l.nummvt AND $requete";
            $connection->executeStatement($sql, $commonParams);

            $sql = "INSERT INTO tmpmvtcaisse (nommvt, num, datemvt, libelle, montant1, temps, codetrs, libtrs, ncompte, solde1, usera) 
                    SELECT 'RS', nummvt, datemvt, CONCAT('RS N° : ', nummvt), mttc, temps, codetrs, libtrs, '', 0, :codeUser 
                    FROM $codeSoc.ebrs AS e 
                    WHERE codefact <> 'A' AND $requete";
            $connection->executeStatement($sql, ['codeUser' => $codeUser] + $commonParams);
        }

        return new JsonResponse(['status' => 'success', 'message' => 'Fiche client détaillée générée avec succès.']);
    }

     
}
