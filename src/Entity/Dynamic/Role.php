<?php

namespace App\Entity\Dynamic;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;  

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string 
    {
        return $this->name;
    }

    public function setName(string $name): static 
    {
        $this->name = $name;
        return $this;
    }
    private $displayName;

    // Ajoutez un getter et setter pour displayName
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;
        return $this;
    }



}
