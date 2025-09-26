<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'Color')]
class Color
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_color')]
    private ?int $id = null;

    #[ORM\Column(name: 'name_color', length: 100)]
    private ?string $nameColor = null;

    #[ORM\OneToMany(mappedBy: 'color', targetEntity: ItemSizeColor::class)]
    private Collection $itemSizeColors;

    public function __construct()
    {
        $this->itemSizeColors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameColor(): ?string
    {
        return $this->nameColor;
    }

    public function setNameColor(string $nameColor): self
    {
        $this->nameColor = $nameColor;
        return $this;
    }

    public function getItemSizeColors(): Collection
    {
        return $this->itemSizeColors;
    }
}


