<?php

namespace App\Entity;

use App\Repository\ExportTempRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExportTempRepository::class)]
#[ORM\Table(name: 'export_temp')]
class ExportTemp
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: 'id_export_temp', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'quantity', type: 'integer')]
    private int $quantity;

    #[ORM\ManyToOne(targetEntity: ItemSizeColor::class)]
    #[ORM\JoinColumn(name: 'id_item_size_color', referencedColumnName: 'id_item_size_color', nullable: false, onDelete: "CASCADE")]
    private ?ItemSizeColor $itemSizeColor = null;

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItemSizeColor(): ?ItemSizeColor
    {
        return $this->itemSizeColor;
    }

    public function setItemSizeColor(?ItemSizeColor $itemSizeColor): static
    {
        $this->itemSizeColor = $itemSizeColor;
        return $this;
    }
}
