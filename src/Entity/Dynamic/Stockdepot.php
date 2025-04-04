<?php

namespace App\Entity\Dynamic;

use App\Repository\StockdepotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Stockdepot
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private ?string $codeart = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $desart = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $codedep = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $libdep = null;


    #[ORM\Column(name: 'libfam', type: 'string', length: 255)]
    private ?string $libfam = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prixvttc1 = null;
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prixvht1 = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $unite = null;
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: self::class)]
    private Collection $tarifarts;

    #[ORM\Column(type: 'float')]
    private ?float $qteart = null;
    #[ORM\Column(type: 'float')]
    private ?float $nature = null;
    

    public function __construct()
    {
        $this->tarifarts = new ArrayCollection();
    }

    public function getTarifarts(): Collection
    {
        return $this->tarifarts;
    }

    public function getCodeart(): ?string
    {
        return $this->codeart;
    }

    public function setCodeart(string $codeart): self
    {
        $this->codeart = $codeart;

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

    public function getDesart()
    {
        return $this->desart;
    }


    public function setDesart($desart)
    {
        $this->desart = $desart;

        return $this;
    }

    public function getCodedep()
    {
        return $this->codedep;
    }

    public function setCodedep($codedep)
    {
        $this->codedep = $codedep;

        return $this;
    }

    public function getLibdep()
    {
        return $this->libdep;
    }

    public function setLibdep($libdep)
    {
        $this->libdep = $libdep;

        return $this;
    }

    public function getQteart()
    {
        return $this->qteart;
    }

    public function setQteart($qteart)
    {
        $this->qteart = $qteart;

        return $this;
    }

    /**
     * Get the value of nature
     */ 
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * Set the value of nature
     *
     * @return  self
     */ 
    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * Get the value of prixvht1
     */ 
    public function getPrixvht1()
    {
        return $this->prixvht1;
    }

    /**
     * Set the value of prixvht1
     *
     * @return  self
     */ 
    public function setPrixvht1($prixvht1)
    {
        $this->prixvht1 = $prixvht1;

        return $this;
    }
}
