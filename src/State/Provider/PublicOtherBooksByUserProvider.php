<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Book;
use App\Repository\BookRepository;

class PublicOtherBooksByUserProvider implements ProviderInterface
{
    private BookRepository $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $bookId = $uriVariables['id'];
        $book = $this->bookRepository->find($bookId);

        if (!$book) {
            throw new \RuntimeException('Book not found');
        }
        
        // Récupérer 3 autres livres du même utilisateur
        return $this->bookRepository->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->andWhere('b.id != :currentBookId')
            ->setParameter('user', $book->getUser())
            ->setParameter('currentBookId', $book->getId())
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
}