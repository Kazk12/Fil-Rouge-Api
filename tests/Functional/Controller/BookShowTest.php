<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Book;
use App\Entity\ProfessionnalDetails;
use App\Entity\State;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class BookShowTest extends WebTestCase
{
    private KernelBrowser $client;
    private ContainerInterface $container;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->container = static::getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function testShowBookNotFound(): void
    {
        $this->client->request('GET', '/api/books/999999');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testShowBookSuccess(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('testoar@example.com');
        $user->setPassword('password');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setRoles(['ROLE_USER']);
        $user->setPhoneNumber('1234567890');
        $user->setDescription('Test Description');
        $prof = new ProfessionnalDetails(); 
        $prof->setCompanyAdress('Test Company Adress');
        $prof->setCompanyName('Test Company Name');
        $user->setProfessionnalDetails($prof);

        $state = new State();
        $state->setName('Test State');
        
        $book = new Book();
        $book->setTitle('Test Book');
        $book->setAuthor('Test Author');
        $book->setDescription('Test Description');
        $book->setShortDescription('Test Short Description');
        $book->setPrice(10);
        $book->setImage('test.jpg');
        $book->setState($state);
        $book->setUser($user);
        
        $this->entityManager->persist($user);
        $this->entityManager->persist($state);
        $this->entityManager->persist($book);
        $this->entityManager->flush();
        
        // Act
        $this->client->request('GET', '/api/books/' . $book->getId());
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('Test Book', $this->client->getResponse()->getContent());
    }
}