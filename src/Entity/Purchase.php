<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\Provider\PurchaseByBuyerProvider;
use App\State\Provider\PurchaseBySellerProvider;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/purchases/by-buyer',
            normalizationContext: ['groups' => ['purchase:read']],
            security: "is_granted('ROLE_USER')",
            provider: PurchaseByBuyerProvider::class,   
        ),
        new GetCollection(
            uriTemplate: '/purchases/by-seller',
            normalizationContext: ['groups' => ['purchase:read']],
            security: "is_granted('ROLE_USER')",
            provider: PurchaseBySellerProvider::class,   
        ),
         new Post (
                uriTemplate: '/purchase',
                denormalizationContext: ['groups' => ['purchase:write']],
                security: "is_granted('ROLE_USER')",
                securityMessage: "Vous devez être connecté pour accéder à cette ressource"
         ),


    ])]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['purchase:read',])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['purchase:read'])]

    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    #[Groups(['purchase:read', 'purchase:write'])]

    private ?string $price = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['purchase:read', 'purchase:write'])]

    private ?User $buyer = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[Groups(['purchase:read', 'purchase:write'])]

    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'sellers')]
    #[Groups(['purchase:read', 'purchase:write'])]

    #[ORM\JoinColumn(nullable: false)]
    private ?User $seller = null;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->statut = "En attente";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getSeller(): ?User
    {
        return $this->seller;
    }

    public function setSeller(?User $seller): static
    {
        $this->seller = $seller;

        return $this;
    }
}
