<?php

namespace App\Controller;

use App\DTO\InvoiceDto;
use App\Entity\Dynamic\Client;
use App\Entity\Dynamic\Ebl;
use App\Entity\Dynamic\Efactv;
use App\Entity\Dynamic\Efc;
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
        $commercial = $this->getUser();
        $codeRep = $commercial->getCode();
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

//     #[Route('fc', name: 'get_fc')]
// public function getFc(Request $request): Response
// {
//     $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
//     $clients = $entityManager->getRepository(Client::class)->findAll();

//     $codetrs = $request->query->get('codetrs');
//     $startDate = $request->query->get('startDate');
//     $endDate = $request->query->get('endDate');

//     $queryBuilder = $entityManager->createQueryBuilder()
//         ->select('e')
//         ->from(Efc::class, 'e')
//         ->orderBy('e.datemvt', 'DESC');
//     if ($codetrs && $startDate && $endDate) {
//         try {
//             $queryBuilder
//                 ->where('e.codetrs = :codetrs')
//                 ->andWhere('e.datemvt BETWEEN :startDate AND :endDate')
//                 ->setParameter('codetrs', $codetrs)
//                 ->setParameter('startDate', new \DateTime($startDate))
//                 ->setParameter('endDate', new \DateTime($endDate));
//         } catch (\Exception $e) {
//             $this->addFlash('error', 'Erreur lors de la récupération des données: ' . $e->getMessage());
//         }
//     }

//     $inovices = $queryBuilder->getQuery()->getResult();

//     return $this->render("admin/fc.html.twig", [
//         'inovices' => $inovices,
//         'clients' => $clients,
//         'selected_codetrs' => $codetrs,
//         'selected_startDate' => $startDate,
//         'selected_endDate' => $endDate
//     ]);
// }
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



    
    
}
