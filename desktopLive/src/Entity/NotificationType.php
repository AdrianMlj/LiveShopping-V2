<?php

namespace App\Entity;

use App\Repository\NotificationTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationTypeRepository::class)]
class NotificationType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_type')]
    private ?int $id = null;

    #[ORM\Column(name: 'name_type', length: 255)]
    private ?string $nameType = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameType(): ?string
    {
        return $this->nameType;
    }

    public function setNameType(string $nameType): static
    {
        $this->nameType = $nameType;
        return $this;
    }
}
