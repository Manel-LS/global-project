<?php
// src/EventListener/LoginListener.php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginListener implements EventSubscriberInterface
{
    private Session $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession(); // Get session from RequestStack
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();
        $databaseChoice = $request->request->get('database_choice');

        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        if ($databaseChoice) {
            $this->session->set('database_choice', $databaseChoice);
        }
    }
}
