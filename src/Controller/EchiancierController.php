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

class EchiancierController extends AbstractController
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
        
    #[Route('/echeancier-client', name: 'ech_client')]
    public function EchClient(Request $request): Response
    {   $session = $this->requestStack->getSession();
        $societe= $session->get('database_choice');
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $clients = $entityManager->getRepository(Client::class)->findAll();       
       return $this->render('admin/ech-client.html.twig', [
            'clients' => $clients,
        ]);
    }

   
    #[Route('/process-payment', name: 'process_payment')]
public function processPayment(Request $request): JsonResponse
{
    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $conn = $entityManager->getConnection();

    // Récupération des paramètres
    $td1 = $request->request->get('td1');
    $td2 = $request->request->get('td2');
    $chkChq = $request->request->get('chkChq') === '1';
    $chkEff = $request->request->get('chkEff') === '1';
    $chkPicais = $request->request->get('piece_caisse') === '1';
    $optEnc = $request->request->get('encaissement') === 'encaisser';
    $optNonEnc = $request->request->get('encaissement') === 'non_encaisser';
    $optTous = $request->request->get('encaissement') === 'tous';
    $tcc = $request->request->get('codetrs');
    $optFact = $request->request->get('etat') === 'facture';
    $optBS = $request->request->get('etat') === 'bs';

    $session = $this->requestStack->getSession();
    $CodeSoc = $session->get('database_choice');
   /** @var \App\Entity\Utilisateur $user */
   $user = $this->getUser();
    $CodeUser = $user->getCode();
    $parametres = $this->getParametres();
    $PARAM = $parametres[0];

    
        try {
             $selectF = " AND impayer='0' AND impcaisse='0' AND modifregl='0' AND endosser='0' AND garantie=0 AND fichecli='1'";
            
             $td1Sql = $this->convertDateToSql($td1);
            $td2Sql = $this->convertDateToSql($td2);
    
             if ($optEnc) {
                $selectF .= " AND encaisser='1' AND echeance BETWEEN :dateDebut AND :dateFin";
            } elseif ($optNonEnc) {
                if ($PARAM->getTresorerie() == "1") {
                    $selectF .= " AND encaisser='0' AND echeance <= :dateFin";
                    if ($chkPicais) {
                        $selectF .= " AND echeance >= :dateDebut AND (numbord='' OR numbord IS NULL)";
                    }
                } else {
                    $selectF .= " AND encaisser='0' AND echeance BETWEEN :dateDebut AND :dateFin";
                }
            } elseif ($optTous) {
                $selectF .= " AND echeance BETWEEN :dateDebut AND :dateFin";
            }
    
            if (!$chkPicais) {
            }
    
            if ($optFact) {
                $selectF .= " AND Baseregl='B'";
            } elseif ($optBS) {
                $selectF .= " AND Baseregl='S'";
            }
    
            if (!empty($tcc)) {
                $selectF .= " AND codetrs = :codeClient";
            }
    
            if ($chkChq && !$chkEff) {
                $selectF .= " AND typeregl='Chèque'";
            } elseif (!$chkChq && $chkEff) {
                $selectF .= " AND typeregl='Effet'";
            } elseif ($chkChq && $chkEff) {
                $selectF .= " AND (typeregl='Effet' OR typeregl='Chèque')";
            }
    
             $params = [
                'dateDebut' => $td1Sql,
                'dateFin' => $td2Sql
            ];
            
            if (!empty($tcc)) {
                $params['codeClient'] = $tcc;
            }
    
            $conn->executeStatement("DELETE FROM baseimpr.reglement WHERE codeuser = :usera", ['usera' => $CodeUser]);
    
            $sourceData = $conn->fetchAllAssociative(
                "SELECT * FROM reglement WHERE 1=1 " . $selectF . " ORDER BY echeance ASC", 
                $params
            );
    
            // Insérer les résultats
            foreach ($sourceData as $result) {
                $conn->insert('baseimpr.reglement', [
                    'codeuser' => $CodeUser,
                    'societe' => $CodeSoc,
                    'codetrs' => $result['codetrs'],
                    'echeance' => $result['echeance'],
                    'montant' => $result['montant'],
                    'typeregl' => $result['typeregl'],
                    'numcheque' => $result['numcheque'],
                    'banquecli' => $result['banquecli'] ?? null,
                    'numbord' => $result['numbord'] ?? null,
                    'encaisser' => $result['encaisser'] ?? 0
                ]);
            }
    
            return $this->json([
                'success' => true,
                'results' => $sourceData,
                'count' => count($sourceData),
                'query' => "SELECT * FROM reglement WHERE 1=1 " . $selectF // Pour debug
            ]);
    
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString() // Pour debug
            ], 500);
        }
    }
    
    private function convertDateToSql(string $date): string
    {
        $parts = explode('/', $date);
        if (count($parts) === 3) {
            return $parts[2].'-'.$parts[1].'-'.$parts[0];
        }
        return $date;
    }
}