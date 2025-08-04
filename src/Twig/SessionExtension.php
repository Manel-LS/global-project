<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SessionExtension extends AbstractExtension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('databaseName', [$this, 'getSessionValue']),
        ];
    }

    public function getSessionValue(string $key)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$request->getSession()) {
            return null;
        }

        return $request->getSession()->get($key);
    }
}
