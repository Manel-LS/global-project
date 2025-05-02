<?php

namespace App\Controller;

use App\Entity\Dynamic\Article;
use App\Entity\Dynamic\Categorie;
use App\Entity\Dynamic\Client;
use App\Entity\Dynamic\Fourchette;
use App\Entity\Dynamic\Stockdepot;
use App\Entity\Dynamic\Userfamille;
use App\Repository\ClientRepository;
use App\Service\DynamicEntityManagerService;
use App\Service\PricingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{

    private $dynamicEntityManagerService;
    private $entityManager;

    public function __construct(
        DynamicEntityManagerService $dynamicEntityManagerService,
        EntityManagerInterface $entityManager,

    ) {
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
        $this->entityManager = $entityManager;

    }

    #[Route('/commande', name: 'app_order')]
    public function index(ClientRepository $clientRepository): Response
    {
       $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $userfamilleRepository = $entityManager->getRepository(Categorie::class);
        $clientRepository = $entityManager->getRepository(Client::class);

        /** @var  \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $codeRep = $user->getCode();
        $clientsData = [];
        $clients = [];
        $categories = $userfamilleRepository->createQueryBuilder('u')
            ->andWhere('u.typefam = :typefam')
            ->setParameter('typefam', 'PF')
            ->getQuery()
            ->getResult();
        // $clients = $clientRepository->createQueryBuilder('c')
        //     ->andWhere('c.coderep = :coderep')
        //     ->setParameter('coderep', $codeRep)
        //     ->getQuery()
        //     ->getResult();
        $clients = $entityManager->getRepository(Client::class)->findAll();       

        foreach ($clients as $client) {
            $clientsData[$client->getCode()] = $client->getLibelle();
        }
        return $this->render("admin/addOrder.html.twig", [
            'clients' => $clients,
            'clientsData' => json_encode($clientsData),
            "categories" => $categories,
        ]);
    }
    #[Route('/articles-by-category-and-code-client/{categoryCode}/{userCode}', name: 'articles-by-category')]
public function getArticles(string $categoryCode, string $userCode, PricingService $pricingService): JsonResponse
{
    $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
    $stockdepotRepository = $entityManager->getRepository(Stockdepot::class);
    $articleRepository = $entityManager->getRepository(Article::class);
    $clientRepository = $entityManager->getRepository(Client::class);
    $categorieRepository = $entityManager->getRepository(Categorie::class);
    $fourchetteRepository = $entityManager->getRepository(Fourchette::class);

    /** @var \App\Entity\Utilisateur $user */
    $user = $this->getUser();
    $client = $clientRepository->findOneBy(['code' => $userCode]);

    if (!$client) {
        return new JsonResponse(['error' => 'Client non trouvé'], 404);
    }

    $categorie = $categorieRepository->findOneBy(['code' => $categoryCode]);

    if (!$categorie) {
        return new JsonResponse(['error' => 'Catégorie non trouvée'], 404);
    }

    $articlesStocks = $stockdepotRepository->createQueryBuilder('s')
        ->where('s.categorie = :categorie')
        ->andWhere('s.qteart > 0')
        ->setParameter('categorie', $categorie)
        ->getQuery()
        ->getResult();

    $articles = $articleRepository->findBy([
        'famille' => $categorie->getCode(),
        'nature' => 'PF'
    ]);

   // $fourchettes = $fourchetteRepository->findAll();
    $artFourchetteCodes = [];
   /* foreach ($fourchettes as $fourchette) {
        $artFourchetteCodes[$fourchette->getCodeart()] = true;
    }*/

    $data = [];

    if (count($articles) > 0) {
        $stockByArticle = [];
        foreach ($articlesStocks as $stock) {
            $stockByArticle[$stock->getCodeart()] = $stock->getQteart();
        }

        foreach ($articles as $article) {
            //$remise = $client->getRemise();
            $tarif = $client->getCtarif();

            if (isset($artFourchetteCodes[$article->getCode()])) {
                $qteArt = 1;
                $discount = $pricingService->calculateDiscount($article->getCode(), $qteArt, $tarif, $remise);
               // $remise = $discount;
            }

            $qteDispo = $stockByArticle[$article->getCode()] ?? 0;

            $prix = round(
                $article->getPrixvttc1()
                * (1 + $article->getFodec() / 100)
                * (1 + $article->getConsvente() / 100)
                * (1 + $article->getTauxtva() / 100),
                3
            );

            $data[] = [
                'code' => $article->getCode(),
                'libelle' => $article->getLibelle(),
                'prix' => $prix,
                'unite' => $article->getUnite(),
                'qteDispo' => $qteDispo,
            ];
        }
    }

    return new JsonResponse($data);
}


    #[Route('/verifier', name: 'verifier')]
    function checkIfArticleIsInFourchette(PricingService $pricingService, Request $request): ?JsonResponse
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $clientRepository = $entityManager->getRepository(Client::class);
        $fourchetteRepository = $entityManager->getRepository(Fourchette::class);

        $code = $request->query->get('code');
        $userCode = $request->query->get('userCode');
        $qteArt = (float) $request->query->get('qteArt');

        $client = $clientRepository->findOneBy(['code' => $userCode]);
        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé'], 404);
        }

        // $fourchette = $fourchetteRepository->findOneBy(['codeart' => $code]);
        $article = $entityManager->getRepository(Article::class)->findOneBy(['code' => $code]);
        if (!$article) {
            return new JsonResponse(['error' => 'Article non trouvé'], 404);
        }

        // if (!$fourchette) {
        //     return new JsonResponse(['success' => 'Article non trouvé dans la fourchette']);
        // }

        $tarif = $client->getCtarif();
       //$remise = $client->getRemise();
     //  $discount = $pricingService->calculateDiscount($code, $qteArt, $tarif, $remise);
     $prix = round(($article->getPrixvttc1() * (1 + $article->getFodec() / 100) * (1 + $article->getConsvente() / 100) * (1 + $article->getTauxtva() / 100)), 3);

        return new JsonResponse(['status' => 'success', 'discount' => $prix]);
    }
}
