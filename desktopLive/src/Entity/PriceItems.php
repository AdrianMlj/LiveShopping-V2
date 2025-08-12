<?php

namespace App\Entity;

use App\Repository\PriceItemsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceItemsRepository::class)]
class PriceItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_price')]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(name: 'date_price', type: 'date')]
    private ?\DateTimeInterface $datePrice = null;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(name: 'id_item', referencedColumnName: 'id_item', nullable: false)]
    private ?Item $item = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getDatePrice(): ?\DateTimeInterface
    {
        return $this->datePrice;
    }

    public function setDatePrice(\DateTimeInterface $datePrice): static
    {
        $this->datePrice = $datePrice;
        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): static
    {
        $this->item = $item;
        return $this;
    }
}
