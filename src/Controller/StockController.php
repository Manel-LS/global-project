<?php

namespace App\Controller;

use App\DTO\InvoiceDto;
use App\Entity\Dynamic\Article;
use App\Entity\Dynamic\Depot;
use App\Entity\Dynamic\Stockdepot;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ParametreRepository;
use App\Service\DynamicEntityManagerService;
use App\Service\FicheClientService;
use App\Service\MtechuClientService;
use App\Service\SoldeAuClientService;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\RequestStack;
class StockController extends AbstractController
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
    #[Route('stock', name: 'stock')]
    public function getstock(): Response
    {
        $session = $this->requestStack->getSession();
        $societe = $session->get('database_choice');
        
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $depotRepository = $entityManager->getRepository(Depot::class); 
        $articleRepository = $entityManager->getRepository(Article::class);
        
        $stockRepository = $entityManager->getRepository(Stockdepot::class);
        $stock = $stockRepository->createQueryBuilder('s')
            ->select('s.codedep, s.libdep, s.codeart, s.desart, s.qteart, s.prixvht1 , s.prixvttc1, s.unite')
            ->where('s.qteart > 0')
            ->andWhere("s.nature = 'pf'")
            ->getQuery()
            ->getResult();
    
        $depots = $depotRepository->findAll();
        $articles = $articleRepository->findAll();
        return $this->render("admin/stock.html.twig", [
            'stock' => $stock,
            'depots' => $depots,
            'articles' => $articles
        ]);
    }
}