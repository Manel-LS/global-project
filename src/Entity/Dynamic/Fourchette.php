<?php

namespace App\Entity\Dynamic;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: "Fourchette_unique", columns: ["codeart","qtemin", "qtemax", "ctarif" , "nligne"])
    ]
)]
class Fourchette
{
    #[ORM\Id]
    #[ORM\Column(name: 'codeart', type: 'string', length: 255)]
    private ?string $codeart = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $desart = null;
    #[ORM\Id]

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $qtemin = null;
    #[ORM\Id]

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $qtemax = null;
    #[ORM\Id]

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $ctarif = null;
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $ajustement = null;


    public function getCodeart()
    {
        return $this->codeart;
    }

  
    public function setCodeart($codeart)
    {
        $this->codeart = $codeart;

        return $this;
    }

    public function getDesart()
    {
        return $this->desart;
    }

  
    public function setDesart($desart)
    {
        $this->desart = $desart;

        return $this;
    }

   
    public function getQtemin()
    {
        return $this->qtemin;
    }

    public function setQtemin($qtemin)
    {
        $this->qtemin = $qtemin;

        return $this;
    }


    public function getQtemax()
    {
        return $this->qtemax;
    }

  
    public function setQtemax($qtemax)
    {
        $this->qtemax = $qtemax;

        return $this;
    }

    public function getCtarif()
    {
        return $this->ctarif;
    }

   
    public function setCtarif($ctarif)
    {
        $this->ctarif = $ctarif;

        return $this;
    }

    
    public function getAjustement()
    {
        return $this->ajustement;
    }

    
    public function setAjustement($ajustement)
    {
        $this->ajustement = $ajustement;

        return $this;
    }
}
