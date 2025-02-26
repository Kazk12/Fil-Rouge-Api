<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserDataPersister implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        if ($data instanceof User) {
            if ($data->getPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPassword());
                $data->setPassword($hashedPassword);
            }
            $data->setRoles(['ROLE_USER']);
            if ($data->getProfessionnalDetails()) {
                $this->entityManager->persist($data->getProfessionnalDetails());
                $data->setRoles(['ROLE_VENDEUR', 'ROLE_USER']);
            }
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $data;
    }
}