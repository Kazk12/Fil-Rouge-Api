<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ProfessionnalDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ProfessionnalDetailsDataPersister implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProfessionnalDetails
    {
        if ($data instanceof ProfessionnalDetails) {
            // Ajoutez ici toute logique supplémentaire avant de persister les données
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $data;
    }
}