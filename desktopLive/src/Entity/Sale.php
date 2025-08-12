<?php

namespace App\Entity;

use App\Repository\SaleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaleRepository::class)]
class Sale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_sale")]
    private ?int $id = null;

    #[ORM\Column(name: 'sale_date', type: 'date')]
    private ?\DateTimeInterface $saleDate = null;

    #[ORM\Column(name: "is_paid")]
    private ?bool $isPaid = null;

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(name: "id_commande", referencedColumnName: "id_commande", nullable: false)]
    private ?Commande $commande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSaleDate(): ?\DateTimeInterface
    {
        return $this->saleDate;
    }

    public function setSaleDate(\DateTimeInterface $saleDate): static
    {
        $this->saleDate = $saleDate;
        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(?bool $isPaid): static
    {
        $this->isPaid = $isPaid;
        return $this;
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
}
