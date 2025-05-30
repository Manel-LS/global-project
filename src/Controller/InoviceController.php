<?php

namespace App\Controller;

use App\DTO\InvoiceDto;
use App\Entity\Dynamic\Client;
use App\Entity\Dynamic\Ebl;
use App\Entity\Dynamic\Ebs;
use App\Entity\Dynamic\Efactv;
use App\Entity\Dynamic\Efc;
use App\Entity\Dynamic\Lbcc;
use App\Entity\Dynamic\Lbl;
use App\Entity\Dynamic\Lbs;
use App\Entity\Dynamic\Lfactv;
use App\Entity\Dynamic\Lfc;
use App\Entity\Dynamic\LigneWeb;
use App\Entity\Dynamic\Reglement;
use App\Repository\ClientRepository;
use App\Repository\EfcRepository;
use App\Repository\LfcRepository;
use App\Repository\LigneWebRepository;
use App\Repository\UtilisateurRepository;
use App\Service\DynamicEntityManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class InoviceController extends AbstractController
{
    private $requestStack;
    private $dynamicEntityManagerService;

    public function __construct(
        RequestStack $requestStack,
        DynamicEntityManagerService $dynamicEntityManagerService,
    ) {
        $this->requestStack = $requestStack;
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
    }

    #[Route('factures', name: 'factures')]
    public function getInovices(EfcRepository $efcRepository, ClientRepository $clientRepository): Response
    {
           /** @var \App\Entity\Utilisateur $user */
           $user = $this->getUser();
           $codeRep = $user->getCode();
        
        $clientsData = [];
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $clients = $entityManager->getRepository(Client::class)->findAll();

        foreach ($clients as $client) {
            $clientsData[$client->getCode()] = $client->getLibelle();
        }

        $inovices = $efcRepository->findFactures($codeRep, $entityManager);
        $inovicesDto = array_map(function ($inovice) {
            return new InvoiceDto(
                $inovice->getNummvt(),
                $inovice->getLibtrs(),
                $inovice->getCodetrs(),
                $inovice->getDatemvt(),
                $inovice->getMttc(),
                $inovice->getTimbref(),
                $inovice->getMtrapp()
            );
        }, $inovices);

        //dd($inovices);
        return $this->render("admin/invoices.html.twig", [
            'inovices' => $inovicesDto,
            'clients' => $clients,
            'clientsData' => json_encode($clientsData),

        ]);
    }
    #[Route('factures_details/{nummvt}', name: 'facturesDetails')]
    public function getInovicesDetails(LfcRepository $LfcRepository, string $nummvt): JsonResponse
    {
        $detailsInovice = $LfcRepository->findFacturesByNummvt($nummvt);
        $factures = [];
        foreach ($detailsInovice as $inovice) {
            $factures[] = [
                'nummvt' => $nummvt,
                'codeart' => $inovice->getCodeart(),
                'desart' => $inovice->getDesart(),
                'qteart' => $inovice->getQteart(),
                'puttc' => round($inovice->getPuttc(), 3),
                'total' => round($inovice->getPuttc() * $inovice->getQteart(), 3),

            ];
        }

        return new JsonResponse($factures);
    }

#[Route('fc', name: 'get_fc')]
public function getFc(Request $request): Response
{
    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $clients = $entityManager->getRepository(Client::class)->findAll();

    $codetrs = $request->query->get('codetrs');
    $startDate = $request->query->get('startDate');
    $endDate = $request->query->get('endDate');

    $queryBuilder = $entityManager->createQueryBuilder()
        ->select('e')
        ->from(Efc::class, 'e')
        ->orderBy('e.datemvt', 'DESC');

    // Construction dynamique des conditions
    if ($codetrs) {
        $queryBuilder
            ->andWhere('e.codetrs = :codetrs')
            ->setParameter('codetrs', $codetrs);
    }

    if ($startDate) {
        try {
            $queryBuilder
                ->andWhere('e.datemvt >= :startDate')
                ->setParameter('startDate', new \DateTime($startDate));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Date de début invalide : ' . $e->getMessage());
        }
    }

    if ($endDate) {
        try {
            $queryBuilder
                ->andWhere('e.datemvt <= :endDate')
                ->setParameter('endDate', new \DateTime($endDate));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Date de fin invalide : ' . $e->getMessage());
        }
    }

    $inovices = $queryBuilder->getQuery()->getResult();

    return $this->render("admin/fc.html.twig", [
        'inovices' => $inovices,
        'clients' => $clients,
        'selected_codetrs' => $codetrs,
        'selected_startDate' => $startDate,
        'selected_endDate' => $endDate
    ]);
}
#[Route('/api/fc-details/{nummvt}', name: 'api_fc_details', methods: ['GET'])]
public function getFcDetails(string $nummvt): JsonResponse
{
    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $details = $entityManager->getRepository(Lfc::class)->findBy(['nummvt' => $nummvt]);
//ssdd($details);
    $result = [];

    foreach ($details as $detail) {
        $result[] = [
            'codeart' => $detail->getCodeart(),
            'desart' => $detail->getDesart(),
            'qteart' => $detail->getQteart(),
            'puht' => $detail->getPuht(),
            'remise' => $detail->getRemise(),
            'tauxtva' => $detail->getTauxtva(),
            'puttc' => $detail->getPuttc(),
            'mttotal' => $detail->getmttotal(),
        ];
    }

    return new JsonResponse($result);
}
#[Route('/api/bs-details/{nummvt}', name: 'bs_details', methods: ['GET'])]
public function getBsDetails(string $nummvt): JsonResponse
{

    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $details = $entityManager->getRepository(Lbs::class)->findBy(['nummvt' => $nummvt]); 
     $data = array_map(function($item) {
        return [
            'codeart' => $item->getCodeart(),
            'desart' => $item->getDesart(),
            'qteart' => $item->getQteart(),
            'puht' => $item->getPuht(),
            'remise' => $item->getRemise(),
            'tauxtva' => $item->getTauxtva(),
            'puttc' => $item->getPuttc(),
            'mttctotal' => $item->getmttotal(),
        ];
    }, $details);
     return $this->json($data);
}
#[Route('/bl/details/{nummvt}', name: 'bl_details')]
public function getBlDetails(string $nummvt): JsonResponse
{
    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $details = $entityManager->getRepository(Lbl::class)->findBy(['nummvt' => $nummvt]); 
 
     
    $data = array_map(function ($line) {
        return [
            'codeart' => $line->getCodeart(),
            'desart' => $line->getDesart(),
            'qteart' => $line->getQteart(),
            'puht' => $line->getPuht(),
            'puttc' => $line->getPuttc(),
            'tauxtva'=> $line->getTauxtva(),
            'remise' => $line->getRemise(),
            'mttotal' => $line->getmttotal(),
        ];
    }, $details);

    return new JsonResponse($data);
}

#[Route('/facture-av/details/{nummvt}', name: 'get_fa_details')]
public function getFaDetails($nummvt): JsonResponse
{
    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $details = $entityManager->getRepository(Lfactv::class)->findBy(['nummvt' => $nummvt]); 
 

    $data = [];
    foreach ($details as $line) {
        $data[] = [
            'codeart' => $line->getCodeart(),
            'desart' => $line->getDesart(),
            'qteart' => $line->getQteart(),
            'puht' => $line->getPuht(),
            'puttc' => $line->getPuttc(),
            'tauxtva'=> $line->getTauxtva(),
            'remise' => $line->getRemise(),
            'mttotal' => $line->getmttotal(),
        ];
      
       
    }

    return $this->json($data);
}


    #[Route('/open-folder/{clientCode}', name: 'open_folder')]
    public function openFolder(string $clientCode): Response
    {
        $baseDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
        $clientDir = $baseDir . $clientCode;

        if (!is_dir($clientDir)) {
            throw $this->createNotFoundException('Dossier client introuvable');
        }
        $windowsPath = str_replace('/', '\\', $clientDir);

        pclose(popen("start explorer \"$windowsPath\"", 'r'));

        return new Response('', 204);
    }

    #[Route('/view-files/{clientCode}', name: 'view_files')]
    public function viewFiles(string $clientCode): Response
    {
        $webPath = '/uploads/' . $clientCode;
        $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $webPath;

        $files = is_dir($fullPath) ? array_diff(scandir($fullPath), ['.', '..']) : [];

        return $this->render('attachments/view.html.twig', [
            'files' => $files,
            'webPath' => $webPath
        ]);
    }

    #[Route('fa', name: 'get_fa')]
public function getFa(Request $request): Response
{
    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $clients = $entityManager->getRepository(Client::class)->findAll();

    $codetrs = $request->query->get('codetrs');
    $startDate = $request->query->get('startDate');
    $endDate = $request->query->get('endDate');

    $queryBuilder = $entityManager->createQueryBuilder()
        ->select('e')
        ->from(Efactv::class, 'e')
        ->orderBy('e.datemvt', 'DESC');
        if ($codetrs) {
            $queryBuilder
                ->andWhere('e.codetrs = :codetrs')
                ->setParameter('codetrs', $codetrs);
        }
    
        if ($startDate) {
            try {
                $queryBuilder
                    ->andWhere('e.datemvt >= :startDate')
                    ->setParameter('startDate', new \DateTime($startDate));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Date de début invalide : ' . $e->getMessage());
            }
        }
    
        if ($endDate) {
            try {
                $queryBuilder
                    ->andWhere('e.datemvt <= :endDate')
                    ->setParameter('endDate', new \DateTime($endDate));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Date de fin invalide : ' . $e->getMessage());
            }
        }
    

    $inovices = $queryBuilder->getQuery()->getResult();

    return $this->render("admin/fa.html.twig", [
        'inovices' => $inovices,
        'clients' => $clients,
        'selected_codetrs' => $codetrs,
        'selected_startDate' => $startDate,
        'selected_endDate' => $endDate
    ]);
}
#[Route('bl', name: 'get_bl')]
public function getBL(Request $request): Response
{
    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $clients = $entityManager->getRepository(Client::class)->findAll();

    $codetrs = $request->query->get('codetrs');
    $startDate = $request->query->get('startDate');
    $endDate = $request->query->get('endDate');

    $queryBuilder = $entityManager->createQueryBuilder()
        ->select('e')
        ->from(Ebl::class, 'e')
        ->orderBy('e.datemvt', 'DESC');
        if ($codetrs) {
            $queryBuilder
                ->andWhere('e.codetrs = :codetrs')
                ->setParameter('codetrs', $codetrs);
        }
    
        if ($startDate) {
            try {
                $queryBuilder
                    ->andWhere('e.datemvt >= :startDate')
                    ->setParameter('startDate', new \DateTime($startDate));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Date de début invalide : ' . $e->getMessage());
            }
        }
    
        if ($endDate) {
            try {
                $queryBuilder
                    ->andWhere('e.datemvt <= :endDate')
                    ->setParameter('endDate', new \DateTime($endDate));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Date de fin invalide : ' . $e->getMessage());
            }
        }
    

    $inovices = $queryBuilder->getQuery()->getResult();

    return $this->render("admin/BL.html.twig", [
        'inovices' => $inovices,
        'clients' => $clients,
        'selected_codetrs' => $codetrs,
        'selected_startDate' => $startDate,
        'selected_endDate' => $endDate
    ]);
}
    #[Route('bs', name: 'get_bs')]
    public function getBS(Request $request): Response
   {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $clients = $entityManager->getRepository(Client::class)->findAll();
    
        $codetrs = $request->query->get('codetrs');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
    
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('e')
            ->from(Ebs::class, 'e')
            ->orderBy('e.datemvt', 'DESC');
            if ($codetrs) {
                $queryBuilder
                    ->andWhere('e.codetrs = :codetrs')
                    ->setParameter('codetrs', $codetrs);
            }
        
            if ($startDate) {
                try {
                    $queryBuilder
                        ->andWhere('e.datemvt >= :startDate')
                        ->setParameter('startDate', new \DateTime($startDate));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Date de début invalide : ' . $e->getMessage());
                }
            }
        
            if ($endDate) {
                try {
                    $queryBuilder
                        ->andWhere('e.datemvt <= :endDate')
                        ->setParameter('endDate', new \DateTime($endDate));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Date de fin invalide : ' . $e->getMessage());
                }
            }
        
    
        $inovices = $queryBuilder->getQuery()->getResult();
    
        return $this->render("admin/BS.html.twig", [
            'inovices' => $inovices,
            'clients' => $clients,
            'selected_codetrs' => $codetrs,
            'selected_startDate' => $startDate,
            'selected_endDate' => $endDate
        ]);
    }
    #[Route('reglement', name: 'reglement')]
    public function getReglement(Request $request): Response
   {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $clients = $entityManager->getRepository(Client::class)->findAll();
    
        $codetrs = $request->query->get('codetrs');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
    
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('e')
            ->from(Reglement::class, 'e')
            ->orderBy('e.datemvt', 'DESC');
            if ($codetrs) {
                $queryBuilder
                    ->andWhere('e.codetrs = :codetrs')
                    ->setParameter('codetrs', $codetrs);
            }
        
            if ($startDate) {
                try {
                    $queryBuilder
                        ->andWhere('e.datemvt >= :startDate')
                        ->setParameter('startDate', new \DateTime($startDate));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Date de début invalide : ' . $e->getMessage());
                }
            }
        
            if ($endDate) {
                try {
                    $queryBuilder
                        ->andWhere('e.datemvt <= :endDate')
                        ->setParameter('endDate', new \DateTime($endDate));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Date de fin invalide : ' . $e->getMessage());
                }
            }
        
    
        $inovices = $queryBuilder->getQuery()->getResult();
    
        return $this->render("admin/reglement.html.twig", [
            'inovices' => $inovices,
            'clients' => $clients,
            'selected_codetrs' => $codetrs,
            'selected_startDate' => $startDate,
            'selected_endDate' => $endDate
        ]);
    }

    #[Route('bon-de-commandes', name: 'bon_de_commandes')]
    public function getBc(): Response
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $ligneWebRepository = $entityManager->getRepository(LigneWeb::class);
        $clientRepository = $entityManager->getRepository(Client::class);

        /** @var  \App\Entity\Utilisateur $commercial */
        $commercial = $this->getUser();
        $codeRep = $commercial->getCode();
        $clientsData = [];
        $clients = [];
        // $clients = $clientRepository->createQueryBuilder('c')
        //     ->andWhere('c.coderep = :coderep')
        //     ->setParameter('coderep', $codeRep)
        //     ->getQuery()
        //     ->getResult();
        $clients = $entityManager->getRepository(Client::class)->findAll();
        foreach ($clients as $client) {
            $clientsData[$client->getCode()] = $client->getLibelle();
        }

        $groupedByNummvt = $this->getGroupOrdersByNumberAndCodeRep($ligneWebRepository, $codeRep);
        return $this->render("admin/bc.html.twig", [
            'inovices' => $groupedByNummvt,
            'clients' => $clients,
            'clientsData' => json_encode($clientsData),

        ]);
    }

    #[Route('bon-de-commandes-by-client/{userCode}', name: 'bon_de_commandes_by_clients')]
    public function getInovicesByClient(string $userCode): JsonResponse
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $ligneWebRepository = $entityManager->getRepository(LigneWeb::class);

        $groupedByNummvt = $ligneWebRepository->createQueryBuilder('l')
            ->select('l')
            ->andWhere('l.codetrs = :codetrs')
            ->setParameter('codetrs', $userCode)
            ->orderBy('l.nummvt')
            ->getQuery()
            ->getResult();;

        if (count($groupedByNummvt) == 0) {
            return new JsonResponse([]);
        }

        $uniqueFactures = [];
        $seenNummvt = [];

        foreach ($groupedByNummvt as $facture) {
            $nummvt = $facture->getNummvt();

            if (!isset($seenNummvt[$nummvt])) {
                $uniqueFactures[] = [
                    'nummvt' => $nummvt,
                    'libtrs' => $facture->getLibtrs(),
                    'datemvt' => $facture->getDatemvt()
                ];
                $seenNummvt[$nummvt] = true;
            }
        }

        return new JsonResponse($uniqueFactures);
    }

    #[Route('bon-de-commandes-details/{nummvt}', name: 'bon_de_commandes_details')]
    public function getBcDetails(int $nummvt): Response
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $ligneWebRepository = $entityManager->getRepository(LigneWeb::class);
        $clientRepository = $entityManager->getRepository(Client::class);

        /** @var \App\Entity\Utilisateur $commercial */
        $commercial = $this->getUser();
        $codeRep = $commercial->getCode();
        $groupedByNummvt = $this->getGroupOrdersByNumberAndCodeRep($ligneWebRepository, $codeRep);

        if (!isset($groupedByNummvt[$nummvt])) {
            throw $this->createNotFoundException("Commande avec le numéro $nummvt introuvable.");
        }

        $detailsInovice = $groupedByNummvt[$nummvt];
        $codeClient = $detailsInovice[0]->getCodetrs();
        $codeRep = $detailsInovice[0]->getCoderep();
        $client = $clientRepository->findOneBy(['code' => $codeClient]);

        $total = array_map(function ($item) {
            return $item->getQteart() * $item->getPuht();
        }, $detailsInovice);

        $totalGlobal = array_sum($total);

        return $this->render("admin/inoviceDetails.html.twig", [
            'numInovice' => $nummvt,
            'detailsInovice' => $detailsInovice,
            'client' => $client,
            'commercial' => $commercial,
            'total' => $total,
            'totalGlobal' => $totalGlobal,
        ]);
    }

    public function getGroupOrdersByNumberAndCodeRep($ligneWebRepository, String $codeRep)
    {
        $nonValideLigneWeb = $ligneWebRepository->createQueryBuilder('l')
            ->select('l')
            ->where('l.valide = :valide')
            ->andWhere('l.coderep = :coderep')
            ->setParameter('valide', 0)
            ->setParameter('coderep', $codeRep)
            ->orderBy('l.nummvt')
            ->getQuery()
            ->getResult();
        $groupedByNummvt = [];

        foreach ($nonValideLigneWeb as $ligne) {
            $nummvt = $ligne->getNummvt();
            if (!isset($groupedByNummvt[$nummvt])) {
                $groupedByNummvt[$nummvt] = [];
            }
            $groupedByNummvt[$nummvt][] = $ligne;
        }
        return $groupedByNummvt;
    }
    
    
}
