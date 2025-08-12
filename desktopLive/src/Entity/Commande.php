<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_commande")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: StateCommande::class)]
    #[ORM\JoinColumn(name: "id_state", referencedColumnName: "id_state", nullable: false)]
    private ?StateCommande $state = null;

    #[ORM\ManyToOne(targetEntity: Bag::class)]
    #[ORM\JoinColumn(name: "id_bag", referencedColumnName: "id_bag", nullable: false)]
    private ?Bag $bag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?StateCommande
    {
        return $this->state;
    }

    public function setState(StateCommande $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getBag(): ?Bag
    {
        return $this->bag;
    }

    public function setBag(Bag $bag): static
    {
        $this->bag = $bag;
        return $this;
    }
}
