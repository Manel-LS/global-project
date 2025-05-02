<?php

namespace App\Controller;

use App\Entity\Dynamic\Article;
use App\Entity\Dynamic\Client;
use App\Entity\Dynamic\Fourchette;
use App\Entity\Dynamic\LigneWeb;
use App\Entity\Dynamic\Structure;
use App\Service\DynamicEntityManagerService;
use App\Service\PricingService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    private $entityManager;


    private $dynamicEntityManagerService;
    public function __construct(
        DynamicEntityManagerService $dynamicEntityManagerService,
        EntityManagerInterface $entityManager,

    ) {
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
        $this->entityManager = $entityManager;
    }

    #[Route('/panier', name: 'app_cart')]
    public function cart(SessionInterface $session): Response
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $articleRepository = $entityManager->getRepository(Article::class);
        /** @var  \App\Entity\Client $user */
        $user = $this->getUser();
        $panier = $session->get('panier', []);
        $data = [];
        $totalGlobal = 0;
        $clientname = '';
        $client = '';

        foreach ($panier as $panierKey => $item) {
            $code = str_replace(['_normal', '_gift', '_exchange'], '', $panierKey);
            $article = $articleRepository->findOneBy(['code' => $code]);

            if (!$article) {
                continue;
            }

            $isGift = strpos($panierKey, '_gift') !== false;
            $isExchange = strpos($panierKey, '_exchange') !== false;

            if (empty($clientname) && isset($item['clientname'])) {
                $clientname = $item['clientname'];
                $client = $item['client'];
            }

            if ($isGift) {
                $data[] = [
                    'article' => $article,
                    'quantityGift' => $item['quantityGift'] ?? 0,
                    'gift' => 1,
                    'exchange' => 0
                ];
            } elseif ($isExchange) {
                $data[] = [
                    'article' => $article,
                    'quantityExchange' => $item['quantityExchange'] ?? 0,
                    'price' => $item['price'] ?? 0,
                    'exchange' => 1,
                    'gift' => 0
                ];
            } else {
                $quantity = $item['quantity'] ?? 0;
                $quantityPromo = $item['quantityPromo'] ?? 0;
                $nbrGratuit = $item['nbrGratuit'] ?? 0;
                $price = $item['price'] ?? 0;
                $newPrice = $item['newPrice'] ?? 0;

                $priceTotal = $newPrice ? $newPrice * $quantity : ($item['priceTotal'] ?? 0);

                $data[] = [
                    'article' => $article,
                    'quantity' => $quantity,
                    'priceTotal' => $priceTotal,
                    'price' => $price,
                    'quantityPromo' => $quantityPromo,
                    'gift' => 0,
                    'exchange' => 0,
                    'nbrGratuit' => $nbrGratuit,
                    'newPrice' => $newPrice,

                ];

                $totalGlobal += $priceTotal;
            }
        }

        return $this->render('admin/cart.html.twig', [
            'data' => $data,
            'totalItems' => count($data),
            'customer' => $user,
            'totalGlobal' => $totalGlobal,
            'clientname' => $clientname,
            'client' => $client
        ]);
    }

    #[Route('/cart/getPanier', name: 'get_panier')]
    public function getPanier(SessionInterface $session): JsonResponse
    {
        $panier = $session->get('panier', []);
        return $this->json($panier);
    }
    #[Route('/add-to-cart/{code}', name: 'add-to-cart')]
    public function add(string $code, Request $request,  PricingService $pricingService, SessionInterface $session): Response
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $articleRepository = $entityManager->getRepository(Article::class);
        $clientRepository = $entityManager->getRepository(Client::class);
        $structureRepository = $entityManager->getRepository(Structure::class);
        $fourchetteRepository = $entityManager->getRepository(Fourchette::class);

        $article = $articleRepository->findOneBy(['code' => $code]);
        if (!$article) {
            return $this->json(['status' => 'error', 'message' => 'Article non trouvé'], 404);
        }
        $codeClient = $request->query->get('client', null);
        $client = $clientRepository->findOneBy(['code' => $codeClient]);

        $panier = $session->get('panier', []);
        $quantity = (int) $request->query->get('quantity', 1);
        $nbrGratuit = $this->getNbrGratuit($structureRepository, $client->getCodeact(), $quantity);

        $price = $request->query->get('price');
        $priceTotal = $request->query->get('priceTotal');
        $quantityPromo = $request->query->get('quantityPromo');
        $selectedClient = $request->query->get('client', null);
        $selectedClientname = $request->query->get('clientname', null);

        $newPrice = $this->checkIfArticleIsInFourchette($fourchetteRepository, $article,  $client,  $pricingService, $code,  $quantity, $price);

        $panierKey = $code . '_normal';
        if ($newPrice != $price) {
            $panier[$panierKey]['newPrice'] = $newPrice;
        }
        if (isset($panier[$article->getCode()]) && isset($panier[$panierKey])) {
            $panier[$article->getCode()]['quantity'] = $quantity;
        } else {
            $panier[$panierKey] = [
                'quantity' => $quantity,
                'price' => $price,
                'priceTotal' => $priceTotal,
                'article' => $article,
                'quantityPromo' => $quantityPromo,
                'client' => $selectedClient,
                'clientname' => $selectedClientname,
                'nbrGratuit' => $nbrGratuit,
                'gift' => 0,
                'newPrice' => $newPrice ?? 0,
            ];
        }
        $session->set('panier', $panier);
        return $this->json([
            'status' => 'success',
            'message' => 'Article ajouté au panier',
            'nbrGratuit' => $nbrGratuit,
        ]);
    }
    public function getNbrGratuit($structureRepository, $codeact, int $quantitySold): int
    {
        $result = $structureRepository->createQueryBuilder('s')
            ->select('s.nbrgratuit, s.nbrvendu')
            ->where('s.code = :codeact')
            ->setParameter('codeact', $codeact)
            ->andWhere('s.typeStruct = :typeStruct')
            ->setParameter('typeStruct', 'AC')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result || !isset($result['nbrgratuit']) || !isset($result['nbrvendu'])) {
            return 0;
        }

        $nbrGratuitBase = (int) $result['nbrgratuit'];
        $nbrVendu = (int) $result['nbrvendu'];

        if ($nbrVendu <= 0 || $quantitySold < $nbrVendu) {
            return 0;
        }

        return intdiv($quantitySold, $nbrVendu) * $nbrGratuitBase;
    }


    #[Route('/add-gift-to-cart/{code}', name: 'add-gift-to-cart')]
    public function addGiftToCart(string $code, Request $request, SessionInterface $session): Response
    {
        try {
            $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
            $articleRepository = $entityManager->getRepository(Article::class);
            $article = $articleRepository->findOneBy(['code' => $code]);

            if (!$article) {
                return $this->json(['status' => 'error', 'message' => 'Article non trouvé'], 404);
            }
            $panier = $session->get('panier', []);
            $quantityGift = (int) $request->query->get('quantityGift', 1);

            $selectedClient = $request->query->get('client', null);
            $selectedClientname = $request->query->get('clientname', null);
            $panierKey = $code . '_gift';

            if (isset($panier[$article->getCode()]) && isset($panier[$panierKey])) {
                $panier[$panierKey]['quantityGift'] = $quantityGift;
            } else {
                $panier[$panierKey] = [
                    'quantityGift' => $quantityGift,
                    'article' => $article,
                    'client' => $selectedClient,
                    'clientname' => $selectedClientname,
                    "gift" => 1
                ];
            }
            $session->set('panier', $panier);

            return $this->json(['status' => 'success', 'message' => 'Article ajouté au panier', 'gift' => '1']);
        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => 'Une erreur est survenue lors de l\'ajout de l\'article au panier : ' . $e->getMessage()]);
        }
    }
    #[Route('/add-exchange-to-cart/{code}', name: 'add-exchange-to-cart')]
    public function addExchangeToCart(string $code, Request $request,  SessionInterface $session): Response
    {
        try {
            $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
            $articleRepository = $entityManager->getRepository(Article::class);
            $article = $articleRepository->findOneBy(['code' => $code]);

            if (!$article) {
                return $this->json(['status' => 'error', 'message' => 'Article non trouvé'], 404);
            }
            $panier = $session->get('panier', []);
            $quantityExchange = (int) $request->query->get('quantityExchange', 1);
            $price = $request->query->get('price');
            $selectedClient = $request->query->get('client', null);
            $selectedClientname = $request->query->get('clientname', null);
            $panierKey = $code . '_exchange';

            if (isset($panier[$article->getCode()]) && isset($panier[$panierKey])) {
                $panier[$panierKey]['quantityExchange'] = $quantityExchange;
            } else {
                $panier[$panierKey] = [
                    'quantityExchange' => $quantityExchange,
                    'article' => $article,
                    'client' => $selectedClient,
                    'clientname' => $selectedClientname,
                    'price' => $price,
                    "gift" => 0,
                    "exchange" => 1

                ];
            }
            $session->set('panier', $panier);

            return $this->json(['status' => 'success', 'message' => 'Article ajouté au panier',  'exchange' => '1']);
        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => 'Une erreur est survenue lors de l\'ajout de l\'article au panier : ' . $e->getMessage()]);
        }
    }
    #[Route('/get-cart-count', name: 'get-cart-count')]
    public function getCartCount(SessionInterface $session): JsonResponse
    {
        $panier = $session->get('panier', []);
        $count = count($panier);
        return $this->json(['count' => $count]);
    }
    #[Route('/update_panier', name: 'update_panier')]
    public function updatePanier(
        Request $request,
        SessionInterface $session,
        PricingService $pricingService,
    ): JsonResponse {
        try {
            $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
            $clientRepository = $entityManager->getRepository(Client::class);
            $articleRepository = $entityManager->getRepository(Article::class);
            $strutureRepository = $entityManager->getRepository(Structure::class);
            $fourchetteRepository = $entityManager->getRepository(Fourchette::class);

            $clientCode = $request->get("clientCode");
            $code = $request->get("code");
            $client = $clientRepository->findOneBy(['code' => $clientCode]);
            $quantity = (float)$request->get('quantity', 0);
            $quantityPromo = (float)$request->get('quantityPromo', 0);
            $quantityGift = (float)$request->get('quantityGift', 0);
            $quantityExchange = (float)$request->get('quantityExchange', 0);

            $isGift = $request->get('isGift');
            $isExchange = $request->get('isExchange');

            if ($isGift == 'true') {
                $panierKey = $code . '_gift';
            } elseif ($isExchange == 'true') {
                $panierKey = $code . '_exchange';
            } else {
                $panierKey = $code . '_normal';
            }

            $panier = $session->get('panier', []);

            if (!isset($panier[$panierKey])) {
                return new JsonResponse(['status' => 'error', 'message' => 'Article non trouvé dans le panier']);
            }

            $article = $articleRepository->findOneBy(['code' => $code]);
            $nbrGratuit = $this->getNbrGratuit($strutureRepository, $client->getCodeact(), $quantity);

            if ($isGift == 'true') {
                $panier[$panierKey]['quantityGift'] = $quantityGift;
            } elseif ($isExchange == 'true') {
                $panier[$panierKey]['quantityExchange'] = $quantityExchange;
            } else {
                $panier[$panierKey]['quantity'] = $quantity;
                $panier[$panierKey]['priceTotal'] = $panier[$panierKey]['price'] * $quantity;

                if ($quantity > 0) {
                    $panier[$panierKey]['nbrGratuit'] = $nbrGratuit;
                }
                $newPrice = $this->checkIfArticleIsInFourchette(
                    $fourchetteRepository,
                    $article,
                    $client,
                    $pricingService,
                    $code,
                    $quantity,
                    $panier[$panierKey]['price']
                );

                if ($newPrice != $panier[$panierKey]['price']) {
                    $panier[$panierKey]['newPrice'] = $newPrice;
                }

                if ($quantityPromo > 0) {
                    $panier[$panierKey]['quantityPromo'] = $quantityPromo;
                }
            }

            $session->set('panier', $panier);

            return new JsonResponse([
                'status' => 'success',
                'price' => $panier[$panierKey]['price'],
                'newPrice' => $newPrice ?? null,
                'nbrGratuit' => $nbrGratuit,
                'panier' => $panier
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour du panier : ' . $e->getMessage()
            ]);
        }
    }

    #[Route('/update_panier_promo', name: 'update_panier_promo')]
    public function updatePanierPromo(Request $request, SessionInterface $session): JsonResponse
    {
        try {
            $code = $request->get("code");
            $quantityPromo = $request->get('quantityPromo');
            $isGift = filter_var($request->get('isGift', false), FILTER_VALIDATE_BOOLEAN);
            $panierKey = $isGift ?  $code . '_gift' : $code . '_normal';
            $panier = $session->get('panier', []);
            $articleExists = isset($panier[$panierKey]);
            $nbrGratuit = null;
            if ($isGift) {
                $nbrGratuit = 0;
            } else {
                $nbrGratuit = $panier[$panierKey]['nbrGratuit'];
            }
            if (!$articleExists) {
                return new JsonResponse(['status' => 'error', 'message' => 'Article non trouvé dans le panier']);
            }
            if (!isset($panier[$panierKey])) {
                return new JsonResponse(['status' => 'error', 'message' => 'Article non trouvé dans le panier']);
            }

            if (isset($quantityPromo) && is_numeric($quantityPromo) && !$isGift) {
                $panier[$panierKey]['quantityPromo'] = $quantityPromo;
            }
            $session->set('panier', $panier);
            return new JsonResponse([
                'status' => 'success',
                'articleExists' => $articleExists,
                'nbrGratuit' => $nbrGratuit,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Une erreur est survenue lors de la mise à jour du panier : ' . $e->getMessage()]);
        }
    }


    #[Route('/retirer-du-panier/{code}/{isGift}/{isExchange}', name: 'remove-from-cart')]
    public function remove(string $code, bool $isGift, bool $isExchange, SessionInterface $session, Request $request): Response
    {
        $panier = $session->get('panier', []);

        if ($isGift) {
            $panierKey = $code . '_gift';
        } else if ($isExchange) {
            $panierKey = $code . '_exchange';
        } else {
            $panierKey = $code . '_normal';
        }

        if (isset($panier[$panierKey])) {
            unset($panier[$panierKey]);
            $session->set('panier', $panier);
        }

        return $this->redirectToRoute('app_cart', ['_fragment' => $request->get('_fragment')]);
    }


    #[Route('/retirer-artcile-du-panier/{code}', name: 'remove_article_cart2')]
    public function removeFromCart2(string $code, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        foreach ($panier as $key => $item) {
            if (is_array($item) && isset($item['article']) && $item['article'] instanceof Article) {
                if ($item['article']->getCode() === $code) {
                    unset($panier[$key]);
                    $session->set('panier', $panier);

                    return new JsonResponse([
                        'status' => 'success',
                        'message' => 'Article supprimé du panier.'
                    ]);
                }
            }
        }

        return new JsonResponse([
            'status' => 'error',
            'message' => 'Article non trouvé dans le panier.'
        ], 404);
    }

    #[Route('/panier/vide', name: 'empty_cart')]
    public function empty(SessionInterface $session)
    {
        $session->remove('panier');
        return $this->redirectToRoute('app_cart');
    }
    #[Route('/panier/cadeaux/vide', name: 'empty_cadeaux')]
    public function emptyCadeaux(SessionInterface $session): JsonResponse
    {
        $panier = $session->get('panier', []);

        foreach ($panier as $key => $item) {
            if (strpos($key, '_gift') !== false) {
                unset($panier[$key]);
            }
        }
        $session->set('panier', $panier);

        return new JsonResponse(['status' => 'success', 'message' => 'Tous les cadeaux ont été supprimés du panier.']);
    }
    #[Route('/panier/retours/vide', name: 'empty_retours')]
    public function emptyRetours(SessionInterface $session): JsonResponse
    {
        $panier = $session->get('panier', []);

        foreach ($panier as $key => $item) {
            if (strpos($key, '_exchange') !== false) {
                unset($panier[$key]);
            }
        }
        $session->set('panier', $panier);

        return new JsonResponse(['status' => 'success', 'message' => 'Tous les cadeaux ont été supprimés du panier.']);
    }

    #[Route('/panier/confirmer', name: 'confirm_order')]
    public function confirmOrder( SessionInterface $session): Response
    {
        $entityManager = $this->dynamicEntityManagerService->getDynamicEntityManager();
        $ligneWebRepository = $entityManager->getRepository(LigneWeb::class);

        /** @var  \App\Entity\Dynamic\Client $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $customerId = $user->getCode();
        $customerNom = $user->getLibelle();
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            return $this->redirectToRoute('app_cart');
        }
        $lastOrder = $ligneWebRepository->createQueryBuilder('l')
            ->orderBy('l.nummvt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $lastOrderNumber = $lastOrder ? $lastOrder->getNummvt() : "000000000";
        $currentDate = new \DateTime();
        $newOrderNumber = $this->generateNewOrderNumber($lastOrderNumber, $currentDate->format('Y-m-d'));
        $currentDateFormatted = $currentDate->format('Y-m-d');
        $currentTimeFormatted = $currentDate->format('H:i');
                try {
            foreach ($panier as $panierKey => $item) {
                $codeArticle = explode('_', $panierKey)[0];
                $article = $entityManager->getRepository(Article::class)->findOneBy(['code' => $codeArticle]);
                if ($article) {
                    $unit = $article->getUnite();
                    $ligneWeb = new LigneWeb();
                    $ligneWeb->setCoderep($customerId);
                    $ligneWeb->setLibrep($customerNom);
                    $ligneWeb->setCodeart($article->getCode());
                    $ligneWeb->setDesart($article->getLibelle());
                    $ligneWeb->setNummvt($newOrderNumber);
                    $ligneWeb->setUnite($unit);
                    $ligneWeb->setDatemvt($currentDateFormatted);
                    $ligneWeb->setValide('0');
                    $ligneWeb->setHeure($currentTimeFormatted);
                    $ligneWeb->setCodetrs($item['client']);
                    $ligneWeb->setLibtrs($item['clientname']);
                    if (isset($item['gift']) && $item['gift'] == 1) {
                        $ligneWeb->setQteart($item['quantityGift']);
                        $ligneWeb->setQtePromo(0);
                        $ligneWeb->setPuttc(0);
                        $ligneWeb->setType("C");
                    }
                    elseif (isset($item['exchange']) && $item['exchange'] == 1) {
                        $ligneWeb->setQteart($item['quantityExchange']);
                        $ligneWeb->setQtePromo(0);
                        $ligneWeb->setPuht($item['price']);
                        $ligneWeb->setType("R");
                    } else {
                        $ligneWeb->setQteart($item['quantity']);
                        $ligneWeb->setQtegratuit($item['nbrGratuit']);
                        $ligneWeb->setQtePromo($item['quantityPromo']);
                        $ligneWeb->setPuht($item['price']);
                        $ligneWeb->setType("V");
                    }
                    $entityManager->persist($ligneWeb);
                    $entityManager->flush();
                    $session->remove('panier');
                }
            }
            return $this->json(['status' => 'ok', 'message' => 'Commande confirmée avec succès.']);
        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    function generateNewOrderNumber(string $lastOrderNumber, string $newDateMvnt): string
    {
        $lastOrderYear = substr($lastOrderNumber, 0, 2);
        $lastOrderSequence = (int)substr($lastOrderNumber, 2);
        $newYear = (int)date('Y', strtotime($newDateMvnt));
        $newYearShort = substr((string)$newYear, 2);
        if ($newYearShort == $lastOrderYear) {
            $newOrderSequence = $lastOrderSequence + 1;
        } else {
            $newOrderSequence = 1;
        }
        $newOrderNumber = $newYearShort . str_pad($newOrderSequence, 9, '0', STR_PAD_LEFT);
        return $newOrderNumber;
    }

    function checkIfArticleIsInFourchette($fourchetteRepository, Article $article, Client $client, PricingService $pricingService, string $code, float $qteArt): ?float
    {
       /* $fourchette = $fourchetteRepository->findOneBy(['codeart' => $code]);
        if (!$fourchette) {
            return null;
        }*/

        $tarif = $client->getCtarif();
     //   $remise = $client->getRemise();

        //$discount = $pricingService->calculateDiscount($code, $qteArt, $tarif);

        $prixBase = $article->getPrixvttc1();
        if ($prixBase === null) {
            throw new \RuntimeException("Le prix de l'article '$code' est indéfini.");
        }

        $prixFinal = $prixBase *
            (1 + $article->getFodec() / 100) *
           // (1 - $discount / 100) *
            (1 + $article->getConsvente() / 100) *
            (1 + $article->getTauxtva() / 100);

        return round($prixFinal, 3);
    }
}
