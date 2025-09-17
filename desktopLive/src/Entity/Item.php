<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_item')]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $images = null;

    #[ORM\Column(name: 'name_item', length: 255)]
    private ?string $nameItem = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_seller', referencedColumnName: 'id_user', nullable: false)]
    private ?Users $seller = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: 'id_category', referencedColumnName: 'id_category', nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: PriceItems::class, orphanRemoval: true)]
    private Collection $priceItems;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: ItemSize::class)]
    private Collection $itemSizes;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: Promotion::class)]
    private Collection $promotions;

    // Getters et setters

    public function __construct()
    {
        $this->priceItems = new ArrayCollection();
        $this->itemSizes = new ArrayCollection();
        $this->promotions = new ArrayCollection();
    }

    // Getters et setters
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function getItemSizes(): Collection
    {
        return $this->itemSizes;
    }

    /**
     * @return Collection<int, PriceItems>
     */
    public function getPriceItems(): Collection
    {
        return $this->priceItems;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameItem(): ?string
    {
        return $this->nameItem;
    }

    public function setNameItem(string $nameItem): static
    {
        $this->nameItem = $nameItem;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;
        return $this;
    }


    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?int $images): self
    {
        $this->images = $images;
        return $this;
    }
}