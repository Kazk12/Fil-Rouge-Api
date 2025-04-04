<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\DataPersister\BookDataPersister;
use App\DataPersister\BookImagePersister;
use App\Repository\BookRepository;
use App\State\Provider\BookCollectionProvider;
use App\State\Provider\PublicOtherBooksByUserProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'author' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'price'])]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['book:read']],
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['book:read']],
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new GetCollection(
            uriTemplate: '/books/{id}/other-books',
            normalizationContext: ['groups' => ['book:read']],
            security: "is_granted('ROLE_USER')",
            provider: BookCollectionProvider::class,
            paginationEnabled: false,
            securityMessage: "Vous devez être connecté pour accéder à cette ressource"
        ),
        new GetCollection(
            uriTemplate: '/books/{id}/public-other-books',
            normalizationContext: ['groups' => ['book:read']],
            security: "is_granted('PUBLIC_ACCESS')",
            provider: PublicOtherBooksByUserProvider::class,
            paginationEnabled: false,
        ),
        new Post(
            denormalizationContext: ['groups' => ['book:write']],
            security: "is_granted('ROLE_VENDEUR')",
            processor: BookDataPersister::class,
            securityMessage: "Seuls les utilisateurs connectés peuvent créer des livres"
        ),
        new Patch(
            denormalizationContext: ['groups' => ['book:write']],
            security: "is_granted('BOOK_EDIT', object)",
            processor: BookDataPersister::class,
            securityMessage: "Vous ne pouvez modifier que vos propres livres"
        ),
        new Delete(
            security: "is_granted('BOOK_DELETE', object)",
            securityMessage: "Vous ne pouvez supprimer que vos propres livres"
        ),
        new Post(
            uriTemplate: '/books/{id}/image',
            security: "is_granted('BOOK_EDIT', object)",
            input: false,
            processor: BookImagePersister::class,
            deserialize: false,
            formats: [
                'json' => ['application/ld+json', 'application/json'],
                'multipart' => ['multipart/form-data']
            ],
            securityMessage: "Vous ne pouvez modifier que vos propres livres"
        ),
    ]
)]

class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['book:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:write', 'book:five'])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Title must be at least {{ limit }} characters long',
        maxMessage: 'Title cannot be longer than {{ limit }} characters',
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Author must be at least {{ limit }} characters long',
        maxMessage: 'Author cannot be longer than {{ limit }} characters',
    )]
    private ?string $author = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'books')]
    #[Groups(['book:read', 'book:write'])]
    private Collection $categories;

    #[ORM\ManyToOne(inversedBy: 'books')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['book:read'])]
    #[ApiProperty(
        openapiContext: [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'prenom' => ['type' => 'string'],
                'nom' => ['type' => 'string'],
            ]
        ]
    )]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    #[Groups(['book:read', 'book:write'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?State $state = null;

    /**
     * @var Collection<int, Purchase>
     */
    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'book')]
    private Collection $purchases;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:write'])]
    #[Assert\Length(
        min: 10,
        minMessage: 'Description must be at least {{ limit }} characters long',
    )]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['book:read', 'book:write'])]
    #[Assert\Length(
        min: 50,
        minMessage: 'Description must be at least {{ limit }} characters long',
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups(['book:read', 'book:write'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $price = null;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:write', 'book:edit'])]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['book:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['book:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->purchases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addBook($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeBook($this);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection<int, Purchase>
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setBook($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            // set the owning side to null (unless already changed)
            if ($purchase->getBook() === $this) {
                $purchase->setBook(null);
            }
        }

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
