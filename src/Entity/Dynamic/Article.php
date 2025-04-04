<?php

namespace App\Entity\Dynamic;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]

class Article
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(name: 'libfam', type: 'string', length: 255)]
    private ?string $libfam = null;
    #[ORM\Column(name: 'nature', type: 'string', length: 255)]
    private ?string $nature = null;
    #[ORM\Column(name: 'fourchprix', type: 'string', length: 1)]
    private ?string $fourchprix = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prixvttc1 = null;
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $tauxtva = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $fodec = null;
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $consvente = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $unite = null;
 

    public function __construct()
    {
     }

  

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getLibfam(): ?string
    {
        return $this->libfam;
    }

    public function setLibfam(string $libfam): self
    {
        $this->libfam = $libfam;
        return $this;
    }


    public function getPrixvttc1(): ?float
    {
        return $this->prixvttc1;
    }

    public function setPrixvttc1(?float $prixvttc1): self
    {
        $this->prixvttc1 = $prixvttc1;

        return $this;
    }
    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): self
    {
        $this->unite = $unite;
        return $this;
    }


    public function getNature()
    {
        return $this->nature;
    }


    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }


    public function getFodec()
    {
        return $this->fodec;
    }


    public function setFodec($fodec)
    {
        $this->fodec = $fodec;

        return $this;
    }


    public function getFourchprix()
    {
        return $this->fourchprix;
    }

    public function setFourchprix($fourchprix)
    {
        $this->fourchprix = $fourchprix;

        return $this;
    }

 
    public function getConsvente()
    {
        return $this->consvente;
    }

    
    public function setConsvente($consvente)
    {
        $this->consvente = $consvente;

        return $this;
    }

  
    public function getTauxtva()
    {
        return $this->tauxtva;
    }


    public function setTauxtva($tauxtva)
    {
        $this->tauxtva = $tauxtva;

        return $this;
    }
}
