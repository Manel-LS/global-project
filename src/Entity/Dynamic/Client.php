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
    private ?string $ctarif = null;
    public function getCtarif()
    {
        return $this->ctarif;
    }


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
}
