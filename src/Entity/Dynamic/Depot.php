<?php

namespace App\Entity\Dynamic;

use App\Repository\StockdepotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Depot
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $responsable = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $adresse = null;



    

    public function __construct()
    {
     }

   

    public function getcode(): ?string
    {
        return $this->code;
    }

    public function setcode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

  

 
  
  
   
    public function getlibelle()
    {
        return $this->libelle;
    }


    public function setlibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getresponsable()
    {
        return $this->responsable;
    }

    public function setresponsable($responsable)
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getadresse()
    {
        return $this->adresse;
    }

    public function setadresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }
 
}
