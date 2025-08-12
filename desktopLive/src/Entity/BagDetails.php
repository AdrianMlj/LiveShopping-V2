<?php

namespace App\Entity;

use App\Repository\BagDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BagDetailsRepository::class)]
class BagDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_bag_detail')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ItemSize::class)]
    #[ORM\JoinColumn(name: 'id_item_size', referencedColumnName: 'id_item_size', nullable: false)]
    private ?ItemSize $itemSize = null;

    #[ORM\ManyToOne(targetEntity: Bag::class)]
    #[ORM\JoinColumn(name: 'id_bag', referencedColumnName: 'id_bag', nullable: false)]
    private ?Bag $bag = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private ?string $price = null;

    // Getters & Setters

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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
