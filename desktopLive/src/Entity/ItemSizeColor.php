<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'Item_size_color')]
class ItemSizeColor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_item_size_color')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ItemSize::class, inversedBy: 'itemSizeColors')]
    #[ORM\JoinColumn(name: 'id_item_size', referencedColumnName: 'id_item_size', nullable: false)]
    private ?ItemSize $itemSize = null;

    #[ORM\ManyToOne(targetEntity: Color::class, inversedBy: 'itemSizeColors')]
    #[ORM\JoinColumn(name: 'id_color', referencedColumnName: 'id_color', nullable: false)]
    private ?Color $color = null;

    #[ORM\Column(name: 'images', length: 255, nullable: true)]
    private ?string $images = null;

    #[ORM\OneToMany(mappedBy: 'itemSizeColor', targetEntity: ItemsStock::class)]
    private Collection $stocks;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemSize(): ?ItemSize
    {
        return $this->itemSize;
    }

    public function setItemSize(ItemSize $itemSize): self
    {
        $this->itemSize = $itemSize;
        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(Color $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): self
    {
        $this->images = $images;
        return $this;
    }

    public function getStocks(): Collection
    {
        return $this->stocks;
    }
}


