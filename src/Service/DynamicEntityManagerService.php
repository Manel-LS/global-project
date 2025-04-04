<?php
  namespace App\Service;

 use Doctrine\ORM\EntityManager;
 use Doctrine\ORM\EntityManagerInterface;
 use Doctrine\ORM\Tools\Setup;
 use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\HttpFoundation\RequestStack;
 
 class DynamicEntityManagerService
 {
     private RequestStack $requestStack;
     private ?EntityManagerInterface $dynamicEntityManager = null;
 
     public function __construct(RequestStack $requestStack)
     {
         $this->requestStack = $requestStack;
     }
 
     public function getDynamicEntityManager(): EntityManagerInterface
     {
         if ($this->dynamicEntityManager) {
             return $this->dynamicEntityManager;
         }
 
         // Récupérer le choix de l'utilisateur depuis la session
         $session = $this->requestStack->getSession();
         $databaseChoice = $session->get('database_choice');
 
         // Construire la connexion dynamique
         $databaseUrl = 'mysql://root:@localhost:3306/' . $databaseChoice;
 
         $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../Entity/Dynamic'],
            true
        );
 
         $params = ['url' => $databaseUrl];
         $connection = DriverManager::getConnection($params);
 
         // Créer un nouvel EntityManager avec la connexion dynamique
         $this->dynamicEntityManager = new EntityManager($connection, $config);
 
         return $this->dynamicEntityManager;
     }
 }
 