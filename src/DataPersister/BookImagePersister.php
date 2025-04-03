<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookImagePersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private SluggerInterface $slugger,
        private string $imageDirectory,
        private LoggerInterface $logger
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
{
    $this->logger->info('BookImageProvider::provide called');

    // Récupérer le livre à partir de l'ID dans l'URI
    if (!isset($uriVariables['id'])) {
        throw new BadRequestHttpException('ID du livre manquant dans l\'URI');
    }

    $book = $this->entityManager->getRepository(Book::class)->find($uriVariables['id']);

    if (!$book) {
        throw new NotFoundHttpException('Livre non trouvé');
    }

    $request = $this->requestStack->getCurrentRequest();

    if (!$request) {
        throw new BadRequestHttpException('Requête invalide');
    }

    $imageFile = $request->files->get('image');

    if (!$imageFile) {
        throw new BadRequestHttpException('Aucune image n\'a été envoyée');
    }

    // Traitement de l'image
    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $this->slugger->slug($originalFilename);
    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

    // Déplacer le fichier
    $imageFile->move($this->imageDirectory, $newFilename);

    // Mettre à jour l'entité Book
    $book->setImage('/images/' . $newFilename);
    $book->setUpdatedAt(new \DateTimeImmutable());

    $this->entityManager->flush();

    // Retourner directement une réponse JSON au lieu de l'entité Book
    return new \Symfony\Component\HttpFoundation\JsonResponse([
        '@context' => '/api/contexts/Book',
        '@id' => '/api/books/' . $book->getId(),
        '@type' => 'Book',
        'id' => $book->getId(),
        'image' => $book->getImage()
    ], 200);
}
}
