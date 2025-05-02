<?php

namespace App\Entity\Dynamic;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Tarifart
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private ?string $famille = null;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $codeart = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $ctarif = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $unitearabe = null;
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prixvttc = null;
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'tarifarts')]
    #[ORM\JoinColumn(name: 'codeart', referencedColumnName: 'code', nullable: false)]
    private ?Article $article = null;

    public function __construct() {}

    public function getFamille(): ?string
    {
        return $this->famille;
    }

    public function setFamille(?string $famille): self
    {
        $this->famille = $famille;
        return $this;
    }

    public function getCodeart(): ?string
    {
        return $this->codeart;
    }

    public function setCodeart(?string $codeart): self
    {
        $this->codeart = $codeart;
        return $this;
    }

    public function getCtarif(): ?string
    {
        return $this->ctarif;
    }

    public function setCtarif(?string $ctarif): self
    {
        $this->ctarif = $ctarif;
        return $this;
    }

    public function getUnitearabe(): ?string
    {
        return $this->unitearabe;
    }

    public function setUnitearabe(?string $unitearabe): self
    {
        $this->unitearabe = $unitearabe;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        return $this;
    }
    public function getPrixvttc(): ?float
    {
        return $this->prixvttc;
    }

    public function setPrixvttc(?float $prixvttc): self
    {
        $this->prixvttc = $prixvttc;

        return $this;
    }
}
