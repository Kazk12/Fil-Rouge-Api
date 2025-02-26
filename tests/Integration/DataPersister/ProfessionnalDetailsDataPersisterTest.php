<?php

// namespace App\Tests\Integration\DataPersister;

// use App\DataPersister\ProfessionnalDetailsDataPersister;
// use App\Entity\ProfessionnalDetails;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

// class ProfessionnalDetailsDataPersisterTest extends KernelTestCase
// {
//     private EntityManagerInterface $entityManager;
//     private ProfessionnalDetailsDataPersister $dataPersister;

//     protected function setUp(): void
//     {
//         $kernel = self::bootKernel();
//         $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
//         $this->dataPersister = new ProfessionnalDetailsDataPersister($this->entityManager);
//     }

//     public function testSupports(): void
//     {
//         $professionnalDetails = new ProfessionnalDetails();
//         $this->assertTrue($this->dataPersister->supports($professionnalDetails));
//     }

//     public function testPersist(): void
//     {
//         $professionnalDetails = new ProfessionnalDetails();
//         $professionnalDetails->setCompanyAdress('123 Main St');
//         $professionnalDetails->setCompanyName('Acme Corp');

//         $this->dataPersister->persist($professionnalDetails);

//         $this->assertNotNull($professionnalDetails->getId());
//     }

//     public function testRemove(): void
//     {
//         $professionnalDetails = new ProfessionnalDetails();
//         $professionnalDetails->setCompanyAdress('123 Main St');
//         $professionnalDetails->setCompanyName('Acme Corp');

//         $this->entityManager->persist($professionnalDetails);
//         $this->entityManager->flush();

//         $id = $professionnalDetails->getId();
//         $this->dataPersister->remove($professionnalDetails);

//         $removedProfessionnalDetails = $this->entityManager->find(ProfessionnalDetails::class, $id);
//         $this->assertNull($removedProfessionnalDetails);
//     }
// }