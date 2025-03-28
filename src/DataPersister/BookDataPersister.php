<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class BookDataPersister implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Book
    {
        if ($data instanceof Book) {
            $currentUser = $this->security->getUser();
            
            if (!$data->getId()) {
                // Nouvelle annonce (création)
                $data->setCreatedAt(new \DateTimeImmutable());
                $data->setUser($currentUser);
            } else {
                // Modification d'une annonce existante
                $data->setUpdatedAt(new \DateTimeImmutable());
            }
            
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $data;
    }
}