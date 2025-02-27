<?php

namespace App\Entity;

use App\Repository\ProfessionnalDetailsRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Attribute\Groups;
use App\DataPersister\ProfessionnalDetailsDataPersister;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessionnalDetailsRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/registerClient',
            denormalizationContext: ['groups' => ['user:write']],
            validationContext: ['groups' => ['Default']],
            security: "is_granted('ROLE_ADMIN')",
            processor: ProfessionnalDetailsDataPersister::class
        ),
        new Get(
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN', 'ROLE_VENDEUR')",

        )
    ]
)]
class ProfessionnalDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:write'])]
    private ?string $companyAdress = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:write'])]
    private ?string $companyName = null;

    #[ORM\OneToOne(mappedBy: 'professionnalDetails', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyAdress(): ?string
    {
        return $this->companyAdress;
    }

    public function setCompanyAdress(string $companyAdress): static
    {
        $this->companyAdress = $companyAdress;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setProfessionnalDetails(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getProfessionnalDetails() !== $this) {
            $user->setProfessionnalDetails($this);
        }

        $this->user = $user;

        return $this;
    }
}