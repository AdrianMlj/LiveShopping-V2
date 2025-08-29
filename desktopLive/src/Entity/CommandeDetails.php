<?php

namespace App\Entity;

use App\Repository\CommandeDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeDetailsRepository::class)]
class CommandeDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_commande_detail")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Commande::class, inversedBy: "details")]
    #[ORM\JoinColumn(name: "id_commande", referencedColumnName: "id_commande", nullable: false)]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(targetEntity: ItemSize::class)]
    #[ORM\JoinColumn(name: "id_item_size", referencedColumnName: "id_item_size", nullable: false)]
    private ?ItemSize $itemSize = null;

    #[ORM\Column(type: "integer")]
    private int $quantity = 1;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(Commande $commande): static
    {
        $this->commande = $commande;
        return $this;
    }

    public function getItemSize(): ?ItemSize
    {
        return $this->itemSize;
    }

    public function setItemSize(ItemSize $itemSize): static
    {
        $this->itemSize = $itemSize;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }
}
