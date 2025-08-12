<?php

namespace App\Entity;

use App\Repository\FavoritesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoritesRepository::class)]
class Favorites
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_favorites')]
    private ?int $id = null;

    #[ORM\Column(name: 'create_at', type: 'datetime')]
    private ?\DateTimeInterface $createAt = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id_user', nullable: false)]
    private ?Users $client = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): static
    {
        $this->createAt = $createAt;
        return $this;
    }

    public function getClient(): ?Users
    {
        return $this->client;
    }

    public function setClient(Users $client): static
    {
        $this->client = $client;
        return $this;
    }
}
