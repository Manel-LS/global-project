<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class CartSubscriber implements EventSubscriberInterface
{
    private $requestStack; // Change de SessionInterface à RequestStack
    private $twig;

    public function __construct(RequestStack $requestStack, Environment $twig) 
    {
        $this->requestStack = $requestStack;
        $this->twig = $twig;
    }

    public function onKernelController()
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request !== null) {
            $session = $request->getSession(); 
            $panier = $session->get('panier', []);
            $nombreArticles = is_array($panier) ? count($panier) : 0;
            $this->twig->addGlobal('nombre_articles', $nombreArticles);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
