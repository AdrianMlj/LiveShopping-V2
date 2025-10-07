<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
#[ORM\Table(name: 'rating')]
#[ORM\UniqueConstraint(name: 'uniq_user_item', columns: ['id_user', 'id_item'])]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_rating')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user', nullable: false, onDelete: 'CASCADE')]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(name: 'id_item', referencedColumnName: 'id_item', nullable: false, onDelete: 'CASCADE')]
    private ?Item $item = null;

    #[ORM\Column(name: 'value', type: 'integer')]
    private int $value = 0;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?Users { return $this->user; }
    public function setUser(Users $user): static { $this->user = $user; return $this; }

    public function getItem(): ?Item { return $this->item; }
    public function setItem(Item $item): static { $this->item = $item; return $this; }

    public function getValue(): int { return $this->value; }
    public function setValue(int $value): static { $this->value = $value; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}
