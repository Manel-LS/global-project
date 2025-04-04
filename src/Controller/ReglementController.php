<?php

namespace App\Controller;

use App\Entity\Dynamic\Client;
use App\Repository\ParametreRepository;
use App\Service\DynamicEntityManagerService;
use App\Service\FicheClientService;
use App\Service\MtechuClientService;
use App\Service\SoldeAuClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;

class ReglementController extends AbstractController
{
    private $requestStack;
    private $dynamicEntityManagerService;
    private $ficheClientService;
    private $mtechuClientService;
    private $soldeAuClientService;
    private $parametreRepository;

    public function __construct(
        RequestStack $requestStack, // Utilisation de RequestStack
        DynamicEntityManagerService $dynamicEntityManagerService,
        FicheClientService $ficheClientService,
        MtechuClientService $mtechuClientService,
        ParametreRepository $parametreRepository,
        SoldeAuClientService $soldeAuClientService
    ) {
        $this->requestStack = $requestStack;
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
        $this->ficheClientService = $ficheClientService;
        $this->mtechuClientService = $mtechuClientService;
        $this->soldeAuClientService = $soldeAuClientService;
        $this->parametreRepository = $parametreRepository;
    }

    public function getParametres(): array
    {
        $session = $this->requestStack->getSession();
        $code = $session->get('database_choice');

        if (!$code) {
            throw new \RuntimeException('Aucun code de base de données trouvé dans la session.');
        }

        return $this->parametreRepository->findByNonClotureAndCode($code);
    }

    #[Route('/releve-clients', name: 'fiche_client')]
    public function showForm(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $societe = $session->get('database_choice');
        //       dd($societe);
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $clients = $entityManager->getRepository(Client::class)->findAll();
        return $this->render('admin/relev-client.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/generate-fiche', name: 'generate-fiche')]
    public function generateReport(Request $request): JsonResponse
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $connection = $entityManager->getConnection();

        $codetrs = $request->request->get('codetrs');
        $startDate = $request->request->get('startDate');
        $endDate = $request->request->get('endDate');
        $choix = $request->request->get('choix');
        $typeFiche = $request->request->get('typeFiche', 'Date');
        $detailMouvement = $request->request->get('detailMouvement', '0');
        $chkdebour = '0';

        $session = $this->requestStack->getSession();
        $CodeSoc = $session->get('database_choice');

        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $codeUser = $user->getCode();

         $connection->executeStatement("DELETE FROM baseimpr.tmpmvtcaisse WHERE usera = :usera", ['usera' => $codeUser]);
        $connection->executeStatement("DELETE FROM baseimpr.lmvt WHERE codeuser = ? AND societe = ?", [$codeUser, $CodeSoc]);

        $requete = "codetrs = :codetrs AND datemvt BETWEEN :startDate AND :endDate";
        $reqMvt = "e.codetrs = :codetrs AND e.datemvt BETWEEN :startDate AND :endDate";
        $reqSel = "AND codetrs = :codetrs AND datemvt < :startDate";
        $reqArt = "AND code = :codetrs";

         $BASE1 = match ($choix) {
            'BL' => 'E',
            'BS' => 'S',
            'facture' => 'F',
            'GL' => 'G',
            'BC' => 'C',
            default => '',
        };

         $parametres = $this->getParametres();
        $PARAM = $parametres[0];

         $this->ficheClientService->ficheClient($codetrs, $startDate, $endDate, $requete, $BASE1, $chkdebour, $CodeSoc, $typeFiche, $connection, $PARAM, $codeUser);
        if ($detailMouvement == "1") {
            $this->ficheClientService->ficheClientDet($codetrs, $startDate, $endDate, $reqMvt, $BASE1, $chkdebour, $CodeSoc, $typeFiche, $connection, $codeUser, $PARAM);
        }

         if ($PARAM->getExerciceCom() > date('Y', strtotime($startDate)) && strlen((string) $PARAM->getExerciceCom()) == 4) {
            $rse = $connection->fetchAllAssociative("SELECT * FROM exercice WHERE anne >= :year ORDER BY anne", ['year' => date('Y', strtotime($startDate))]);

            foreach ($rse as $row) {
                $this->ficheClientService->ficheClient($codetrs, $startDate, $endDate, $requete, $BASE1, $chkdebour, $row['CodeSoc'], $typeFiche, $connection, $PARAM, $codeUser);

                if ($detailMouvement == "1") {
                    $this->ficheClientService->ficheClientDet($codetrs, $startDate, $endDate, $reqMvt, $BASE1, $chkdebour, $row['CodeSoc'], $typeFiche, $connection, $codeUser, $PARAM);
                }
            }
        }

         $solde = $this->soldeAuClientService->soldeAuClient(
            $reqSel,
            $reqArt,
            $BASE1,
            $chkdebour,
            $CodeSoc,
            $typeFiche,
            $connection,
            $PARAM,
            $codetrs,
            $startDate
        );

        $connection->executeStatement(
            "INSERT INTO baseimpr.tmpmvtcaisse (nommvt, num, datemvt, libelle, montant, temps, codetrs, libtrs, ncompte, solde1, usera)
         SELECT 'S.I', 'S.I', :dateReport, CONCAT('Report Au : ', :dateReport), 0, '000000000000000', code, libelle, '', solde, :usera
         FROM client WHERE code = :codetrs",
            [
                'dateReport' => date('Y-m-d', strtotime($startDate . ' -1 day')),
                'usera' => $codeUser,
                'codetrs' => $codetrs,
            ]
        );

        $rsj = $connection->fetchAllAssociative("SELECT DISTINCT codetrs FROM baseimpr.tmpmvtcaisse WHERE usera = :usera ORDER BY codetrs", ['usera' => $codeUser]);

        foreach ($rsj as $row) {
            $rsau = $connection->fetchAssociative("SELECT * FROM baseimpr.tmpmvtcaisse WHERE codetrs = :codetrs", ['codetrs' => $row['codetrs']]);

            if ($rsau) {
                $connection->executeStatement(
                    "UPDATE baseimpr.tmpmvtcaisse SET solde = :solde, tel = :tel, fax = :fax, gsm = :gsm, adresse = :adresse, codetva = :codetva WHERE codetrs = :codetrs AND usera = :usera",
                    [
                        'solde' => $rsau['montant'],
                        'tel' => $rsau['tel'],
                        'fax' => $rsau['fax'],
                        'gsm' => $rsau['gsm'],
                        'adresse' => $rsau['adresse'],
                        'codetva' => $rsau['codetva'],
                        'codetrs' => $row['codetrs'],
                        'usera' => $codeUser,
                    ]
                );
            }
        }

        $baseData = $connection->fetchAllAssociative(
            "SELECT 
            datemvt, 
            num, 
            libelle, 
            libcaisse, 
            echeance, 
            montant, 
            montant1, 
            solde1, 
            nommvt,
            '' as codeart,
            '' as designation,
            'base' as data_type
         FROM baseimpr.tmpmvtcaisse 
         WHERE codetrs = :codetrs AND usera = :usera 
         ORDER BY datemvt, num",
            [
                'codetrs' => $codetrs,
                'usera' => $codeUser,
            ]
        );

        $detailData = [];
        if ($detailMouvement == "1") {
            $detailData = $connection->fetchAllAssociative(
                "SELECT 
                datemvt, 
                nummvt as num, 
                lcaisse, 
                '' as libcaisse, 
                '' as echeance, 
                mttotal as mttotal, 
                puttc as puttc, 
                qteart as qteart, 
                0 as montant1, 
                0 as solde1, 
                '' as nommvt,
                codeart, 
                desart as designation,
                'detail' as data_type
             FROM baseimpr.lmvt 
             WHERE codetrs = :codetrs AND codeuser = :usera AND societe = :societe
             ORDER BY datemvt, nummvt",
                [
                    'codetrs' => $codetrs,
                    'usera' => $codeUser,
                    'societe' => $CodeSoc,
                ]
            );
        }

         $selectionData = array_merge($baseData, $detailData);

         $mtechu = $this->mtechuClientService->mtechuCli(new \DateTime($endDate), $codetrs, $CodeSoc, $BASE1, $connection, $PARAM);
        $mtencour = $this->mtechuClientService->MTEncourCli(new \DateTime($endDate), $codetrs, $BASE1, $connection, $PARAM);
        $mtsoldeImpayer = $this->mtechuClientService->mtsoldeImpayer($codetrs, $connection);
        $mtchqGarClient = $this->mtechuClientService->mtchqGarClient($codetrs, $connection);

         if ($choix === 'facture' && $PARAM->getCalcLivCour() === "0") {
            $livEncours = 0.000;
        } elseif ($choix === 'facture') {
            $livEncours = $this->mtechuClientService->livEncours($codetrs, "EBL", $endDate, $connection);
        } else {
            $livEncours = 0.000;
        }

         $mtTotalRisque = $solde + $livEncours + $mtencour + $mtechu;

        return new JsonResponse([
            'selectionData' => $selectionData,
            'baseData' => $baseData, 
            'detailData' => $detailData, 
            'mtechu' => $mtechu,
            'mtencour' => $mtencour,
            'mtsoldeImpayer' => $mtsoldeImpayer,
            'mtchqGarClient' => $mtchqGarClient,
            'soldeini' => $solde,
            'livEncours' => $livEncours,
            'livraisonEncours' => $livEncours,
            'mtTotalRisque' => $mtTotalRisque,
            'detailMouvement' => $detailMouvement
        ]);
    }
}
