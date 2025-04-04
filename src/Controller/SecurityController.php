<?php

namespace App\Controller;

use App\Entity\Global\Parametre;
use App\Repository\ParametreRepository;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils,Request $request, SessionInterface $session, ParametreRepository $parametreRepository): Response
    {
        
        $parametres = $parametreRepository->findBy(['cloturer' => 0]);


        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $databaseChoice = $request->get('database_choice');
        if ($databaseChoice) {
            $session->set('database_choice', $databaseChoice);
        }

         $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'parametres' => $parametres,
            'error' => $error,   
            'last_username' => $lastUsername,   
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
