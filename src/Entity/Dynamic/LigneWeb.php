<?php

namespace App\Entity\Dynamic;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ligneweb')]
class LigneWeb
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nummvt = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $datemvt = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $dateliv = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $heure = null;

    #[ORM\Column(name: 'codeart', type: 'string', length: 255)]
    private ?string $codeart = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $desart = null;

    #[ORM\Column(type: 'float', length: 255)]
    private ?float $qteart = null;

    // #[ORM\Column(type: 'float', length: 255)]
    // private ?float $qtepromo = null;

    #[ORM\Column(type: 'float', length: 255)]
    private ?float $qtegratuit = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $codetrs = null;
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $coderep = null;


    #[ORM\Column(type: 'string', length: 255)]
    private ?string $libtrs = null;
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $librep = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $unite = null;


    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $puttc = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $puht = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $valide = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $type = null;


    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNummvt(): ?string
    {
        return $this->nummvt;
    }

    public function setNummvt(string $nummvt): self
    {
        $this->nummvt = $nummvt;
        return $this;
    }

    public function getDatemvt(): ?string
    {
        return $this->datemvt;
    }

    public function setDatemvt(string $datemvt): self
    {
        $this->datemvt = $datemvt;
        return $this;
    }
    public function getDateliv(): ?string
    {
        return $this->dateliv;
    }

    public function setDateliv(string $dateliv): self
    {
        $this->dateliv = $dateliv;
        return $this;
    }

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(string $heure): self
    {
        $this->heure = $heure;
        return $this;
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

    public function getDesart(): ?string
    {
        return $this->desart;
    }

    public function setDesart(string $desart): self
    {
        $this->desart = $desart;
        return $this;
    }

    public function getCodetrs(): ?string
    {
        return $this->codetrs;
    }
    public function SetCodetrs(string $codetrs): self
    {
        $this->codetrs = $codetrs;
        return $this;
    }
    public function getCoderep(): ?string
    {
        return $this->coderep;
    }
    public function SetCoderep(string $coderep): self
    {
        $this->coderep = $coderep;
        return $this;
    }

    public function getLibtrs(): ?string
    {
        return $this->libtrs;
    }
    public function SetLibtrs(string $libtrs): self
    {
        $this->libtrs = $libtrs;
        return $this;
    }
    public function getLibrep(): ?string
    {
        return $this->librep;
    }
    public function setLibrep(string $librep): self
    {
        $this->librep = $librep;
        return $this;
    }
    public function getUnite(): ?string
    {
        return $this->unite;
    }
    public function SetUnite(string $unite): self
    {
        $this->unite = $unite;
        return $this;
    }



    public function getPuttc(): ?float
    {
        return $this->puttc;
    }

    public function setPuttc(?float $puttc): self
    {
        $this->puttc = $puttc;

        return $this;
    }

    public function getValide()
    {
        return $this->valide;
    }

    public function setValide($valide)
    {
        $this->valide = $valide;

        return $this;
    }

 
    public function getType()
    {
        return $this->type;
    }


    public function setType($type)
    {
        $this->type = $type;

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

    // public function getQtepromo()
    // {
    //     return $this->qtepromo;
    // }

    // public function setQtepromo($qtepromo)
    // {
    //     $this->qtepromo = $qtepromo;

    //     return $this;
    // }
 
    public function getQtegratuit()
    {
        return $this->qtegratuit;
    }

    public function setQtegratuit($qtegratuit)
    {
        $this->qtegratuit = $qtegratuit;

        return $this;
    }

    public function getPuht()
    {
        return $this->puht;
    }

 
    public function setPuht($puht)
    {
        $this->puht = $puht;

        return $this;
    }
}
