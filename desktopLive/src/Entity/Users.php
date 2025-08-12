<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'Users')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_user')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 500)]
    private ?string $password = null;

    #[ORM\Column(length: 10)]
    private ?string $contact = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 500)]
    private ?string $country = null;

    #[ORM\Column(type:'text', nullable: true)]
    private ?string $images = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isSeller = null;

    // Getters et setters

    public function getRoles(): array
    {
        // retourne toujours un rôle au minimum
        return ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): static
    {
        $this->contact = $contact;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): static
    {
        $this->images = $images;
        return $this;
    }

    public function isSeller(): ?bool
    {
        return $this->isSeller;
    }

    public function setIsSeller(bool $isSeller): static
    {
        $this->isSeller = $isSeller;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username; // ou $this->username si tu veux te connecter avec username
    }

    public function eraseCredentials(): void
    {
        // Si tu stockes un mot de passe en clair temporaire, tu le nettoies ici.
        // Exemple : $this->plainPassword = null;
    }

}
