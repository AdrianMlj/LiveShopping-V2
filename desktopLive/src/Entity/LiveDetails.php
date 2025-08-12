<?php

namespace App\Entity;

use App\Repository\LiveDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LiveDetailsRepository::class)]
class LiveDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_live_detail')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(name: 'id_item', referencedColumnName: 'id_item', nullable: false)]
    private ?Item $item = null;

    #[ORM\ManyToOne(targetEntity: Live::class)]
    #[ORM\JoinColumn(name: 'id_live', referencedColumnName: 'id_live', nullable: false)]
    private ?Live $live = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLive(): ?Live
    {
        return $this->live;
    }

    public function setLive(Live $live): static
    {
        $this->live = $live;
        return $this;
    }
}
