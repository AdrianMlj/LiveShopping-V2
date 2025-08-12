<?php

namespace App\Entity;

use App\Repository\SizeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SizeRepository::class)]
class Size
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_size')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nameSize = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameSize(): ?string
    {
        return $this->nameSize;
    }

    public function setNameSize(string $nameSize): static
    {
        $this->nameSize = $nameSize;

        return $this;
    }
}
