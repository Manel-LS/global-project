<?php

namespace App\Entity\Dynamic;

use App\Repository\ReglementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReglementRepository::class)]
class Reglement
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 11)]
    private ?string $nummvt = null;
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $datemvt = null;
    #[ORM\Column(type: 'string', length: 80)]
    private ?string $libtrs = null;
    #[ORM\Column(type: 'string', length: 8)]
    private ?string $codetrs = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $montant = null;
    #[ORM\Column(type: 'float', nullable: true)]
    
     
    private ?string $coderep = null;


    public function getNummvt()
    {
        return $this->nummvt;
    }

    public function setNummvt($nummvt)
    {
        $this->nummvt = $nummvt;

        return $this;
    }

    public function getDatemvt()
    {
        return $this->datemvt;
    }

    public function setDatemvt($datemvt)
    {
        $this->datemvt = $datemvt;

        return $this;
    }

    public function getLibtrs()
    {
        return $this->libtrs;
    }

    public function setLibtrs($libtrs)
    {
        $this->libtrs = $libtrs;

        return $this;
    }

    public function getCodetrs()
    {
        return $this->codetrs;
    }

    public function setCodetrs($codetrs)
    {
        $this->codetrs = $codetrs;

        return $this;
    }

    public function getmontant()
    {
        return $this->montant;
    }

    public function setmontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }
   
    public function getCoderep()
    {
        return $this->coderep;
    }

    public function setCoderep($coderep)
    {
        $this->coderep = $coderep;

        return $this;
    }
 
    
}
