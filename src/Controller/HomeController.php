<?php

namespace App\Controller;

use App\Entity\Lbcc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClientRepository; // Ajoute cette ligne
use App\Repository\UsersRepository;
use App\Service\CartService;
use App\Service\DynamicConnectionService;
use App\Service\DynamicEntityManagerService;
use App\Service\PermissionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{

    private DynamicEntityManagerService $dynamicEntityManagerService;

    public function __construct(DynamicEntityManagerService $dynamicEntityManagerService)
    {
        $this->dynamicEntityManagerService = $dynamicEntityManagerService;
    } 
    // #[Route('/', name: 'app_home')]
    // public function index(): Response
    // {
    //     return $this->render('admin/dashboard.html.twig', [
           
    //     ]);
    // }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('admin/dashboard.html.twig');
    }
}
