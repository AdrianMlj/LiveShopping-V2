<?php

namespace App\Entity;

use App\Repository\StateCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StateCommandeRepository::class)]
class StateCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_state")]
    private ?int $id = null;

    #[ORM\Column(name: "name_state", length: 255)]
    private ?string $nameState = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameState(): ?string
    {
        return $this->nameState;
    }

    public function setNameState(string $nameState): static
    {
        $this->nameState = $nameState;
        return $this;
    }
}
