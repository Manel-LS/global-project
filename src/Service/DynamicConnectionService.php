<?php
// src/Service/DynamicConnectionService.php
// src/Service/DynamicConnectionService.php
namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DynamicConnectionService
{
    private ManagerRegistry $managerRegistry;
    private SessionInterface $session;

    public function __construct(ManagerRegistry $managerRegistry, SessionInterface $session)
    {
        $this->managerRegistry = $managerRegistry;
        $this->session = $session;
    }
    public function getDynamicConnection(): Connection
    {
        // Récupérer le nom de la base de données dynamique depuis la session
        $databaseName = $this->session->get('database_choice');

        if (!$databaseName) {
            throw new \RuntimeException('Aucune base de données dynamique sélectionnée.');
        }

        // Construire l'URL de connexion dynamique
        $connectionParams = [
            'url' => "mysql://root:@127.0.0.1:3306/$databaseName",
            'driver' => 'mysql',
            'charset' => 'utf8mb4',
        ];

        // Créer et retourner la connexion
        return DriverManager::getConnection($connectionParams);
    }
    
}