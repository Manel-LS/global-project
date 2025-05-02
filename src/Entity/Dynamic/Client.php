<?php
namespace App\Entity\Dynamic;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'client')]
class Client
{
    #[ORM\Id]
    #[ORM\Column]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $cin = null;
    #[ORM\Column(length: 255)]
    private ?string $gsm = null;
    #[ORM\Column(length: 5)]
    private ?string $bloque = null;
    #[ORM\Column(length: 5)]
    private ?string $codeact = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $scredit = null;
   
    #[ORM\Column(length: 255)]
    private ?string $coderep = null;

       
    #[ORM\Column(length: 255)]
    private ?string $tel2 = null;


    
    
   
    #[ORM\Column(length: 255)]
    private ?string $ctarif = null;
    
    
    #[ORM\Column(length: 255)]
    private ?string $codetva = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;
     
    
    public function getId(): ?string
    {
        return $this->code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }



    public function getCodeact(): ?string
    {
        return $this->codeact;
    }

    public function setCodeact(string $codeact): static
    {
        $this->codeact = $codeact;
        return $this;
    }

    public function getBloque(): ?string
    {
        return $this->bloque;
    }

    public function setBloque(string $bloque): static
    {
        $this->bloque = $bloque;
        return $this;
    }
    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): static
    {
        $this->cin = $cin;
        return $this;
    }
    public function getGsm(): ?string
    {
        return $this->gsm;
    }

    public function setGsm(string $gsm): static
    {
        $this->gsm = $gsm;
        return $this;
    }



    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void {}


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
     * Get the value of scredit
     */
    public function getScredit()
    {
        return $this->scredit;
    }

    /**
     * Set the value of scredit
     *
     * @return  self
     */
    public function setScredit($scredit)
    {
        $this->scredit = $scredit;

        return $this;
    }

    /**
     * Get the value of coderep
     */ 
    public function getCoderep()
    {
        return $this->coderep;
    }

    /**
     * Set the value of coderep
     *
     * @return  self
     */ 
    public function setCoderep($coderep)
    {
        $this->coderep = $coderep;

        return $this;
    }
 

    /**
     * Get the value of ctarif
     */ 
    public function getCtarif()
    {
        return $this->ctarif;
    }

    /**
     * Set the value of ctarif
     *
     * @return  self
     */ 
    public function setCtarif($ctarif)
    {
        $this->ctarif = $ctarif;

        return $this;
    }

    /**
     * Get the value of codetva
     */ 
    public function getCodetva()
    {
        return $this->codetva;
    }

    /**
     * Set the value of codetva
     *
     * @return  self
     */ 
    public function setCodetva($codetva)
    {
        $this->codetva = $codetva;

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
     * Get the value of tel2
     */ 
    public function getTel2()
    {
        return $this->tel2;
    }

    /**
     * Set the value of tel2
     *
     * @return  self
     */ 
    public function setTel2($tel2)
    {
        $this->tel2 = $tel2;

        return $this;
    }
}
