<?php

namespace App\Entity;

use App\Repository\BagRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: BagRepository::class)]
class Bag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_bag')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime', name: 'create_at')]
    private \DateTimeInterface $createAt;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isCommande = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id_user', nullable: false)]
    private ?Users $client = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_seller', referencedColumnName: 'id_user', nullable: false)]
    private ?Users $seller = null;

    #[ORM\OneToMany(mappedBy: 'bag', targetEntity: BagDetails::class)]
    private Collection $bagDetails;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreateAt(): \DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): static
    {
        $this->createAt = $createAt;
        return $this;
    }

    public function getIsCommande(): ?bool
    {
        return $this->isCommande;
    }

    public function setIsCommande(?bool $isCommande): static
    {
        $this->isCommande = $isCommande;
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

    /**
     * @return Collection<int, BagDetails>
     */
    public function getBagDetails(): Collection
    {
        return $this->bagDetails;
    }
}
