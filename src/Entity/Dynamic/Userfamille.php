<?php

namespace App\Entity\Dynamic;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Userfamille
{
    #[ORM\Id]
    #[ORM\Column(length: 8)]
    private ?string $famille = null;

    #[ORM\Column(length: 100)]
    private ?string $libfam = null;

    #[ORM\Column(length: 8)]
    private ?string $codeuser = null;
    
    #[ORM\Column(length: 30)]
    private ?string $libelle = null;


    public function getFamille()
    {
        return $this->famille;
    }


    public function setFamille($famille)
    {
        $this->famille = $famille;

        return $this;
    }

  
    public function getLibfam()
    {
        return $this->libfam;
    }


    public function setLibfam($libfam)
    {
        $this->libfam = $libfam;

        return $this;
    }

    public function getCodeuser()
    {
        return $this->codeuser;
    }


    public function setCodeuser($codeuser)
    {
        $this->codeuser = $codeuser;

        return $this;
    }


    public function getLibelle()
    {
        return $this->libelle;
    }

    
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }
}
