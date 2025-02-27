<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;

class LastFiveBooksProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Book[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->entityManager->getRepository(Book::class)
            ->findBy([], ['id' => 'DESC'], 5);
    }
}