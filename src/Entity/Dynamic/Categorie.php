<?php

namespace App\Entity\Dynamic;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: 'famille')]

class Categorie
{
    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private ?string $code = null;  
    #[ORM\Column(length: 50)]
    private ?string $libelle = null;  
    #[ORM\Column(length: 50)]
    private ?string $typefam = null;  


    public function getCode(): ?string 
    {
        return $this->code;
    }

    public function setCode(string $code): static 
    {
        $this->code = $code;
        return $this;
    }
 
    public function getLibelle(): ?string 
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static 
    {
        $this->libelle = $libelle;
        return $this;
    }



    public function getTypefam()
    {
        return $this->typefam;
    }

   
    public function setTypefam($typefam)
    {
        $this->typefam = $typefam;

        return $this;
    }
}
