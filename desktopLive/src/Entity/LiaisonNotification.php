<?php

namespace App\Entity;

use App\Repository\LiaisonNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LiaisonNotificationRepository::class)]
class LiaisonNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_liaison")]
    private ?int $id = null;

    #[ORM\Column(name: "name_table", length: 50)]
    private ?string $nameTable = null;

    #[ORM\Column(name: "id_table")]
    private ?int $idTable = null;

    #[ORM\ManyToOne(targetEntity: Notification::class)]
    #[ORM\JoinColumn(name: "id_notification", referencedColumnName: "id_notification", nullable: false)]
    private ?Notification $notification = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameTable(): ?string
    {
        return $this->nameTable;
    }

    public function setNameTable(string $nameTable): static
    {
        $this->nameTable = $nameTable;
        return $this;
    }

    public function getIdTable(): ?int
    {
        return $this->idTable;
    }

    public function setIdTable(int $idTable): static
    {
        $this->idTable = $idTable;
        return $this;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(Notification $notification): static
    {
        $this->notification = $notification;
        return $this;
    }
}
