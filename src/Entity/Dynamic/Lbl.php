<?php

namespace App\Entity\Dynamic;

use Doctrine\ORM\Mapping as ORM;
 
#[ORM\Entity]
#[ORM\Table(name: 'lbl')]
class Lbl
{
    #[ORM\Id]

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nummvt = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $datemvt = null;
   
    #[ORM\Column(name: 'codeart', type: 'string', length: 255)]
    private ?string $codeart = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $desart = null;
  
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $qteart = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $codetrs = null;
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $coderep = null;
    

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $libtrs = null;
    
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $unite = null;

  
    #[ORM\Column(type: 'float', length: 255)]
    private ?float $poids = null;
    
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $puttc = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $tauxtva = null;
  
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $remise = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $puht = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $mttotal = null;
    

     public function __construct()
     {
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
 
    public function getTauxtva()
    {
        return $this->tauxtva;
    }

    public function setTauxtva($tauxtva)
    {
        $this->tauxtva = $tauxtva;

        return $this;
    }
   
    public function getMttotal()
    {
        return $this->mttotal;
    }

    public function setMttotal($mttotal)
    {
        $this->mttotal = $mttotal;

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
    public function getQteart(): ?string
    {
        return $this->qteart;
    }
    public function SetQteart(string $qteart): self
    {
        $this->qteart = $qteart;
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


    
    
   
    public function getRemise(): ?float
    {
        return $this->remise;
    }

     public function setRemise(?float $remise): self
    {
        $this->remise = $remise;

        return $this;
    }
    
    public function getPuht(): ?float
    {
        return $this->puht;
    }

     public function setPuht(?float $puht): self
    {
        $this->puht = $puht;

        return $this;
    }
    /**
     * Get the value of poids
     */ 
    public function getPoids()
    {
        return $this->poids;
    }

    /**
     * Set the value of poids
     *
     * @return  self
     */ 
    public function setPoids($poids)
    {
        $this->poids = $poids;

        return $this;
    }
}
