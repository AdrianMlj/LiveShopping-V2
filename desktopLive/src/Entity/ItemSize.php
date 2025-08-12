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

    #[ORM\OneToMany(mappedBy: 'itemSize', targetEntity: ItemsStock::class)]
    private Collection $stocks;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
    }

    // Getters et setters
    public function getStocks(): Collection
    {
        return $this->stocks;
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
