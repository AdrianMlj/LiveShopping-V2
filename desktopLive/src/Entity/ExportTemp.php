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

    #[ORM\ManyToOne(targetEntity: ItemSize::class)]
    #[ORM\JoinColumn(name: 'id_item_size', referencedColumnName: 'id_item_size', nullable: false, onDelete: "CASCADE")]
    private ?ItemSize $itemSize = null;

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

    public function getItemSize(): ?ItemSize
    {
        return $this->itemSize;
    }

    public function setItemSize(?ItemSize $itemSize): static
    {
        $this->itemSize = $itemSize;
        return $this;
    }
}
