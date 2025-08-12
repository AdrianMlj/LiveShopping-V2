<?php

namespace App\Entity;

use App\Repository\FollowSellerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FollowSellerRepository::class)]
class FollowSeller
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_follow')]
    private ?int $id = null;

    #[ORM\Column(name: 'date_following' , type: 'datetime')]
    private ?\DateTimeInterface $dateFollowing = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id_user', nullable: false)]
    private ?Users $client = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_seller', referencedColumnName: 'id_user', nullable: false)]
    private ?Users $seller = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateFollowing(): ?\DateTimeInterface
    {
        return $this->dateFollowing;
    }

    public function setDateFollowing(\DateTimeInterface $dateFollowing): static
    {
        $this->dateFollowing = $dateFollowing;
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

    public function getSeller(): ?Users
    {
        return $this->seller;
    }

    public function setSeller(Users $seller): static
    {
        $this->seller = $seller;
        return $this;
    }
}
