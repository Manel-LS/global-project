<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 * @ORM\Table(name="utilisateur") 
 */
#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['login'], message: 'هناك حساب موجود بهذا الاسم بالفعل.')] // Unicité du login
#[UniqueEntity(fields: ['code'], message: 'هذا الرمز مستخدم بالفعل.')] // Unicité du code
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'يرجى إدخال رمز التسجيل.')]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'يرجى إدخال اسم المستخدم.')]
    #[Assert\Length(
        min: 4,
        max: 50,
        minMessage: 'يجب أن يحتوي اسم المستخدم على الأقل 4 أحرف.',
        maxMessage: 'يجب ألا يتجاوز اسم المستخدم 50 حرفًا.'
    )]
    private ?string $login = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $libelle = null;





    #[ORM\Column(name: "motpasse", length: 255)]

    private ?string $motpasse = null;


    #[ORM\Column(length: 50, nullable: true)]
    private ?string $actif = null;





    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->motpasse;
    }

    public function getMotpasse(): ?string
    {
        return $this->motpasse;
    }
    public function setMotpasse(string $motpasse): static
    {
        $this->motpasse = $motpasse;
        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }
    public function setLibelle(?string $libelle): static
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getActif(): ?string
    {
        return $this->actif;
    }

    public function setActif(?string $actif): static
    {
        $this->actif = $actif;
        return $this;
    }
    public function getRoles(): array
    {
        return [];
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }
    public function eraseCredentials(): void
    {
         
    }

    /**
     * Get the value of numcaisse
     */

    /**
     * Set the value of numcaisse
     *
     * @return  self
     */
}
