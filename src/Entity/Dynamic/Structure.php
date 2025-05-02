<?php

namespace App\Entity\Dynamic;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: "Structure_unique", columns: ["code","TypeStruct"])
    ]
)]
class Structure
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private ?string $code = null;
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $libelle = null;
    #[ORM\Id]
    #[ORM\Column(name:'TypeStruct' ,type: 'string', length: 255)]
    private ?string $typeStruct = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $nbrgratuit = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $nbrvendu = null;


    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;

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

  

    public function getNbrgratuit()
    {
        return $this->nbrgratuit;
    }

    public function setNbrgratuit($nbrgratuit)
    {
        $this->nbrgratuit = $nbrgratuit;

        return $this;
    }

    
    public function getTypeStruct()
    {
        return $this->typeStruct;
    }

  
    public function setTypeStruct($typeStruct)
    {
        $this->typeStruct = $typeStruct;

        return $this;
    }

   
    public function getNbrvendu()
    {
        return $this->nbrvendu;
    }

    
    public function setNbrvendu($nbrvendu)
    {
        $this->nbrvendu = $nbrvendu;

        return $this;
    }
}
