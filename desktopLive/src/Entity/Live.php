<?php

namespace App\Entity;

use App\Repository\LiveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LiveRepository::class)]
class Live
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_live')]
    private ?int $id = null;

    #[ORM\Column(name: 'start_live', type: 'datetime')]
    private ?\DateTimeInterface $startLive = null;

    #[ORM\Column(name: 'end_live', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $endLive = null;

    #[ORM\Column(name: 'nbr_like', type: 'integer', nullable: true)]
    private ?int $nbrLike = null;

    #[ORM\Column(name: 'titre', type: 'string', length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_seller', referencedColumnName: 'id_user', nullable: false)]
    private ?Users $seller = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartLive(): ?\DateTimeInterface
    {
        return $this->startLive;
    }

    public function setStartLive(\DateTimeInterface $startLive): static
    {
        $this->startLive = $startLive;
        return $this;
    }

    public function getEndLive(): ?\DateTimeInterface
    {
        return $this->endLive;
    }

    public function setEndLive(?\DateTimeInterface $endLive): static
    {
        $this->endLive = $endLive;
        return $this;
    }

    public function getNbrLike(): ?int
    {
        return $this->nbrLike;
    }

    public function setNbrLike(?int $nbrLike): static
    {
        $this->nbrLike = $nbrLike;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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
