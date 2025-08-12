<?php

namespace App\Entity;

use App\Repository\ItemsStockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemsStockRepository::class)]
class ItemsStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_item_stock')]
    private ?int $id = null;

    #[ORM\Column(name: 'out_item', type: 'integer', nullable: true)]
    private ?int $outItem = null;

    #[ORM\Column(name: 'in_item', type: 'integer', nullable: true)]
    private ?string $inItem = null;

    #[ORM\Column(name: 'date_move', type: 'datetime')]
    private ?\DateTimeInterface $dateMove = null;

    #[ORM\ManyToOne(targetEntity: ItemSize::class)]
    #[ORM\JoinColumn(name: 'id_item_size', referencedColumnName: 'id_item_size', nullable: false)]
    private ?ItemSize $itemSize = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOutItem(): ?int
    {
        return $this->outItem;
    }

    public function setOutItem(?int $outItem): static
    {
        $this->outItem = $outItem;
        return $this;
    }

    public function getInItem(): ?string
    {
        return $this->inItem;
    }

    public function setInItem(?string $inItem): static
    {
        $this->inItem = $inItem;
        return $this;
    }

    public function getDateMove(): ?\DateTimeInterface
    {
        return $this->dateMove;
    }

    public function setDateMove(\DateTimeInterface $dateMove): static
    {
        $this->dateMove = $dateMove;
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
}
