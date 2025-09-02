<?php

namespace App\Entity;

use App\Repository\GoalsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoalsRepository::class)]
#[ORM\Table(name: "Goals")]
class Goals
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_goal = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Users")]
    #[ORM\JoinColumn(name: "id_seller", referencedColumnName: "id_user", nullable: false, onDelete: "CASCADE")]
    private ?\App\Entity\Users $seller = null;

    #[ORM\Column(type: "decimal", precision: 15, scale: 2)]
    private float $target_ca;

    #[ORM\Column(type: "integer")]
    private int $target_ventes;

    // ---------- Getters et Setters ----------

    public function getIdGoal(): ?int
    {
        return $this->id_goal;
    }

    public function getSeller(): ?\App\Entity\Users
    {
        return $this->seller;
    }

    public function setSeller(\App\Entity\Users $seller): self
    {
        $this->seller = $seller;
        return $this;
    }

    public function getTargetCa(): float
    {
        return $this->target_ca;
    }

    public function setTargetCa(float $target_ca): self
    {
        $this->target_ca = $target_ca;
        return $this;
    }

    public function getTargetVentes(): int
    {
        return $this->target_ventes;
    }

    public function setTargetVentes(int $target_ventes): self
    {
        $this->target_ventes = $target_ventes;
        return $this;
    }
}
