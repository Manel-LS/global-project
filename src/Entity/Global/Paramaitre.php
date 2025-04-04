<?php

namespace App\Entity\Global;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

 
#[ORM\Entity]
class Paramaitre
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $ExerciceCom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $cloturer = null;
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $TypeRapp = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $CalcLivCour = null;
    
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $ParamEchu = null;
    
     
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $Tresorerie = null;
    
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $gestionbs = null;
    


    /**
     * Get the value of code
     */ 
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @return  self
     */ 
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of libelle
     */ 
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set the value of libelle
     *
     * @return  self
     */ 
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get the value of adresse
     */ 
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set the value of adresse
     *
     * @return  self
     */ 
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get the value of cloturer
     */ 
    public function getCloturer()
    {
        return $this->cloturer;
    }

    /**
     * Set the value of cloturer
     *
     * @return  self
     */ 
    public function setCloturer($cloturer)
    {
        $this->cloturer = $cloturer;

        return $this;
    }

    /**
     * Get the value of TypeRapp
     */ 
    public function getTypeRapp()
    {
        return $this->TypeRapp;
    }

    /**
     * Set the value of TypeRapp
     *
     * @return  self
     */ 
    public function setTypeRapp($TypeRapp)
    {
        $this->TypeRapp = $TypeRapp;

        return $this;
    }

    /**
     * Get the value of ExerciceCom
     */ 
    public function getExerciceCom()
    {
        return $this->ExerciceCom;
    }

    /**
     * Set the value of ExerciceCom
     *
     * @return  self
     */ 
    public function setExerciceCom($ExerciceCom)
    {
        $this->ExerciceCom = $ExerciceCom;

        return $this;
    }

    /**
     * Get the value of CalcLivCour
     */ 
    public function getCalcLivCour()
    {
        return $this->CalcLivCour;
    }

    /**
     * Set the value of CalcLivCour
     *
     * @return  self
     */ 
    public function setCalcLivCour($CalcLivCour)
    {
        $this->CalcLivCour = $CalcLivCour;

        return $this;
    }

    /**
     * Get the value of ParamEchu
     */ 
    public function getParamEchu()
    {
        return $this->ParamEchu;
    }

    /**
     * Set the value of ParamEchu
     *
     * @return  self
     */ 
    public function setParamEchu($ParamEchu)
    {
        $this->ParamEchu = $ParamEchu;

        return $this;
    }

    /**
     * Get the value of Tresorerie
     */ 
    public function getTresorerie()
    {
        return $this->Tresorerie;
    }

    /**
     * Set the value of Tresorerie
     *
     * @return  self
     */ 
    public function setTresorerie($Tresorerie)
    {
        $this->Tresorerie = $Tresorerie;

        return $this;
    }

    /**
     * Get the value of gestionbs
     */ 
    public function getGestionbs()
    {
        return $this->gestionbs;
    }

    /**
     * Set the value of gestionbs
     *
     * @return  self
     */ 
    public function setGestionbs($gestionbs)
    {
        $this->gestionbs = $gestionbs;

        return $this;
    }
}