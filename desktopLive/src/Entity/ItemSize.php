<?php

namespace App\Entity;

use App\Repository\ItemSizeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ItemSizeRepository::class)]
class ItemSize
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_item_size')]
    private ?int $id = null;

    #[ORM\Column(name: 'value_size', length: 50, nullable: true)]
    private ?string $valueSize = null;

    #[ORM\ManyToOne(targetEntity: Size::class)]
    #[ORM\JoinColumn(name: 'id_size', referencedColumnName: 'id_size', nullable: false)]
    private ?Size $size = null;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(name: 'id_item', referencedColumnName: 'id_item', nullable: false)]
    private ?Item $item = null;

    // Relation de stock déplacée vers ItemSizeColor → ItemsStock

    #[ORM\OneToMany(mappedBy: 'itemSize', targetEntity: ItemSizeColor::class, orphanRemoval: true)]
    private Collection $itemSizeColors;

    public function __construct()
    {
        $this->itemSizeColors = new ArrayCollection();
    }

    // Getters et setters
    // Note: plus de getStocks() ici; utilisez ItemSizeColor->getStocks()

    public function getItemSizeColors(): Collection
    {
        return $this->itemSizeColors;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValueSize(): ?string
    {
        return $this->valueSize;
    }

    public function setValueSize(?string $valueSize): static
    {
        $this->valueSize = $valueSize;
        return $this;
    }

    public function getSize(): ?Size
    {
        return $this->size;
    }

    public function setSize(Size $size): static
    {
        $this->size = $size;
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
