<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class SoldeAuClientService
{
    private $dynamicEntityManagerService;
 
    public function __construct(DynamicEntityManagerService $dynamicEntityManagerService)
    {
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
    }

 

 function soldeAuClient($ReqSel, $ReqArt, $BaseF, $Debours, $CodeSoc, $TypeFiche, Connection $connection, $PARAM, $codetrs, $startDate)
{
    $ReqRegl = "";
    $ChampIni = "";

    // Déterminer les conditions en fonction de BaseF
    if ($BaseF == "E" || $BaseF == "C") {
        $ReqRegl = " AND baseregl = 'B' AND garantie='0' ";
        $ChampIni = " soldeini ";
    } elseif ($BaseF == "F") {
        $ReqRegl = " AND (baseregl = 'B' OR baseregl='F') AND garantie='0' ";
        $ChampIni = " soldeini ";
    } elseif ($BaseF == "S") {
        $ReqRegl = " AND baseregl = 'S' AND garantie='0' ";
        $ChampIni = " soldeini1 ";
    } elseif ($BaseF == "G") {
        $ReqRegl = " AND (baseregl = 'B' OR baseregl='S') AND garantie='0' ";
        $ChampIni = " ( soldeini + soldeini1 ) ";
    }

     $Requete = "SELECT code AS codetrs, $ChampIni AS soldef, 0 AS simp, 0 AS livcour, 'SI' FROM $CodeSoc.client WHERE 1=1 $ReqArt ";
    $Requete .= " UNION ";

     if ($TypeFiche == "Date") {
        $Requete .= "SELECT codetrs, SUM(-montant) AS soldef, 0 AS simp, 0 AS livcour, 'RG' FROM $CodeSoc.reglement WHERE fichecli='1' $ReqSel $ReqRegl GROUP BY codetrs";
        $Requete .= " UNION ";
    } elseif ($TypeFiche == "Ech") {
        $Requete .= "SELECT codetrs, SUM(-montant) AS soldef, 0 AS simp, 0 AS livcour, 'RG' FROM $CodeSoc.reglement WHERE fichecli='1' AND (typeregl <>'Chèque' AND typeregl <>'Effet') $ReqSel $ReqRegl GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-montant) AS soldef, 0 AS simp, 0 AS livcour, 'RG' FROM $CodeSoc.reglement WHERE fichecli='1' AND (typeregl = 'Chèque' OR typeregl = 'Effet') " . str_replace("datemvt", "echeance", $ReqSel) . $ReqRegl . " GROUP BY codetrs";
        $Requete .= " UNION ";
    } elseif ($TypeFiche == "Enc") {
        $Requete .= "SELECT codetrs, SUM(-montant) AS soldef, 0 AS simp, 0 AS livcour, 'RG' FROM $CodeSoc.reglement WHERE fichecli='1' AND (typeregl <>'Chèque' AND typeregl <>'Effet') $ReqSel $ReqRegl GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-montant) AS soldef, 0 AS simp, 0 AS livcour, 'RG' FROM $CodeSoc.reglement WHERE fichecli='1' AND (typeregl = 'Chèque' OR typeregl = 'Effet') " . str_replace("datemvt", "dateenc", $ReqSel) . $ReqRegl . " GROUP BY codetrs";
        $Requete .= " UNION ";
    }

     if ($Debours == "1") {
        $Requete .= "SELECT codetrs, SUM(-resteventiler) AS soldef, 0 AS simp, 0 AS livcour, 'DE' FROM $CodeSoc.regldeb WHERE ROUND(resteventiler, 3) > 0 $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
    }

     if ($BaseF != "S") {
        $Requete .= "SELECT codetrs, SUM(montant + fraisimp) AS soldef, 0 AS simp, 0 AS livcour, 'IMP' FROM $CodeSoc.impclient WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl <>'S') $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(montant) AS soldef, 0 AS simp, 0 AS livcour, 'IMC' FROM $CodeSoc.impcaisse WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl <>'S') $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(montant) AS soldef, 0 AS simp, 0 AS livcour, 'MOD' FROM $CodeSoc.modifregl WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl <>'S') $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
    } else {
        $Requete .= "SELECT codetrs, SUM(montant + fraisimp) AS soldef, 0 AS simp, 0 AS livcour, 'IMPS' FROM $CodeSoc.impclient WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl ='S') $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(montant) AS soldef, 0 AS simp, 0 AS livcour, 'IMCS' FROM $CodeSoc.impcaisse WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl ='S') $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(montant) AS soldef, 0 AS simp, 0 AS livcour, 'MODS' FROM $CodeSoc.modifregl WHERE pieceliee IN (SELECT nummvt FROM reglement WHERE baseregl ='S') $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
    }

    // Ajouter les autres requêtes
    $Requete .= "SELECT codetrs, SUM(mttc + timbref) AS soldef, 0 AS simp, 0 AS livcour, 'FC' FROM $CodeSoc.efc WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
    $Requete .= " UNION ";
    $Requete .= "SELECT codetrs, 0 AS soldef, SUM(montant + fraisimp - mtrapp) AS simp, 0 AS livcour, 'IMPE' FROM $CodeSoc.impclient WHERE 1=1 $ReqSel GROUP BY codetrs";
    $Requete .= " UNION ";
    $Requete .= "SELECT codetrs, 0 AS soldef, 0 AS simp, SUM(mttc) AS livcour, 'BLE' FROM $CodeSoc.ebl WHERE codefact= 'N' $ReqSel GROUP BY codetrs";
    $Requete .= " UNION ";
    $Requete .= "SELECT codetrs, 0 AS soldef, 0 AS simp, SUM(-mttc) AS livcour, 'BRE' FROM $CodeSoc.ebrc WHERE codefact= 'N' $ReqSel GROUP BY codetrs";
    $Requete .= " UNION ";

    // Ajouter les conditions en fonction de BaseF
    if ($BaseF == "F") {
        $Requete .= "SELECT codetrs, SUM(-mttc - timbref) AS soldef, 0 AS simp, 0 AS livcour, 'AV' FROM $CodeSoc.efactv WHERE typefact = 'AV' AND codefact<> 'A' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(mttc) AS soldef, 0 AS simp, 0 AS livcour, 'BL' FROM $CodeSoc.ebl WHERE codefact='N' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-mttc) AS soldef, 0 AS simp, 0 AS livcour, 'BR' FROM $CodeSoc.ebrc WHERE codefact='N' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(mttc + timbref) AS soldef, 0 AS simp, 0 AS livcour, 'FA' FROM $CodeSoc.efactv WHERE typefact = 'FA' AND codefact<> 'A' $ReqSel GROUP BY codetrs";
    } elseif ($BaseF == "E") {
        $Requete .= "SELECT codetrs, SUM(mttc) AS soldef, 0 AS simp, 0 AS livcour, 'BS' FROM $CodeSoc.ebs WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-mttc) AS soldef, 0 AS simp, 0 AS livcour, 'RS' FROM $CodeSoc.ebrs WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-mttc - timbref) AS soldef, 0 AS simp, 0 AS livcour, 'AV' FROM $CodeSoc.efactv WHERE typefact = 'AV' AND codefact<> 'A' AND numbc='AV_FIN' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-mttc) AS soldef, 0 AS simp, 0 AS livcour, 'BR' FROM $CodeSoc.ebrc WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(mttcnet) AS soldef, 0 AS simp, 0 AS livcour, 'TC' FROM $CodeSoc.etick WHERE (codefact= 'N' OR codefact='O' OR codefact = '0' OR codefact IS NULL) $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
    } elseif ($BaseF == "G") {
        $Requete .= "SELECT codetrs, SUM(mttc) AS soldef, 0 AS simp, 0 AS livcour, 'BS' FROM $CodeSoc.ebs WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-mttc) AS soldef, 0 AS simp, 0 AS livcour, 'RS' FROM $CodeSoc.ebrs WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-mttc - timbref) AS soldef, 0 AS simp, 0 AS livcour, 'AV' FROM $CodeSoc.efactv WHERE typefact = 'AV' AND codefact<> 'A' AND numbc='AV_FIN' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-mttc) AS soldef, 0 AS simp, 0 AS livcour, 'BR' FROM $CodeSoc.ebrc WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(mttcnet) AS soldef, 0 AS simp, 0 AS livcour, 'TC' FROM $CodeSoc.etick WHERE (codefact= 'N' OR codefact='O' OR codefact = '0' OR codefact IS NULL) $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
    } elseif ($BaseF == "S") {
        $Requete .= "SELECT codetrs, SUM(mttc) AS soldef, 0 AS simp, 0 AS livcour, 'BS' FROM $CodeSoc.ebs WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
        $Requete .= " UNION ";
        $Requete .= "SELECT codetrs, SUM(-mttc) AS soldef, 0 AS simp, 0 AS livcour, 'RS' FROM $CodeSoc.ebrs WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
    } elseif ($BaseF == "C") {
        $Requete .= "SELECT codetrs, SUM(mttc) AS soldef, 0 AS simp, 0 AS livcour, 'BC' FROM $CodeSoc.ebcc WHERE codefact<> 'A' $ReqSel GROUP BY codetrs";
    }

     $Requete = rtrim($Requete, " UNION ");

     $Req = "SELECT c1, c2, tel, gsm, fax, adresse, codetva, (somme) AS SoldeFin, (somimp) AS soldeimp, (somlivcour) AS livencours 
            FROM (SELECT L.code AS c1, L.libelle AS c2, L.tel1 AS tel, l.gsm AS gsm, l.fax AS fax, l.adresse AS adresse, l.codetva AS codetva, 
                         tot.somme AS somme, tot.somimp AS somimp, tot.somlivcour AS somlivcour 
                  FROM $CodeSoc.client L, 
                       (SELECT lcall.codetrs, SUM(lcall.soldef) AS somme, SUM(lcall.simp) AS somimp, SUM(lcall.livcour) AS somlivcour 
                        FROM ($Requete) AS lcall 
                        GROUP BY lcall.codetrs) AS tot 
                  WHERE l.code = tot.codetrs) AS tableau";

     $params = [
        'codetrs' => $codetrs,
        'startDate' => $startDate
    ];

    $result = $connection->executeQuery($Req, $params);
    $data = $result->fetchAssociative();

    return round($data['SoldeFin'] ?? 0, 3);
}
  
}