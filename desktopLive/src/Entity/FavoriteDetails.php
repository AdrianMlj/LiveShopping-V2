<?php

namespace App\Entity;

use App\Repository\FavoriteDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteDetailsRepository::class)]
class FavoriteDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_favorite_detail')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ItemSize::class)]
    #[ORM\JoinColumn(name: 'id_item_size', referencedColumnName: 'id_item_size', nullable: false)]
    private ?ItemSize $itemSize = null;

    #[ORM\ManyToOne(targetEntity: Favorites::class)]
    #[ORM\JoinColumn(name: 'id_favorites', referencedColumnName: 'id_favorites', nullable: false)]
    private ?Favorites $favorites = null;

    // Getters & Setters

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

    public function getFavorites(): ?Favorites
    {
        return $this->favorites;
    }

    public function setFavorites(Favorites $favorites): static
    {
        $this->favorites = $favorites;
        return $this;
    }
}
