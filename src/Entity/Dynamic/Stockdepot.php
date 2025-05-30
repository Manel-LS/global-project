<?php

namespace App\Entity\Dynamic;

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

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: 'famille', referencedColumnName: 'code', nullable: false)]
    private Categorie $categorie;
    #[ORM\Column(name: 'libfam', type: 'string', length: 255)]
    private ?string $libfam = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prixvttc1 = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $unite = null;

    #[ORM\Column(type: 'float')]
    private ?float $qteart = null;

    #[ORM\Column(type: 'float')]
    private ?float $nature = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prixvht1 = null;

    public function getCodeart(): ?string
    {
        return $this->codeart;
    }

    public function setCodeart(string $codeart): self
    {
        $this->codeart = $codeart;

        return $this;
    }

    public function getCategorie(): Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(Categorie $categorie): self
    {
        $this->categorie = $categorie;

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

    public function getNature()
    {
        return $this->nature;
    }
  
    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }
  
    public function getPrixvht1()
    {
        return $this->prixvht1;
    }

    public function setPrixvht1($prixvht1)
    {
        $this->prixvht1 = $prixvht1;

        return $this;
    }
}
