<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Book;
use App\Entity\User;
use App\Security\Voter\BookVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class BookVoterTest extends TestCase
{
    private BookVoter $voter;
    
    protected function setUp(): void
    {
        $this->voter = new BookVoter();
    }

    // Test si un utilisateur propriétaires d'un livre peut le modifier
    public function testVoteOnEditForBookOwner(): void
    {
        // Arrange
        $user = new User();
        $book = new Book();
        $book->setUser($user);
        
        /** @var TokenInterface&\PHPUnit\Framework\MockObject\MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // Act
        $result = $this->voter->vote($token, $book, [BookVoter::EDIT]);

        // Assert
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testEditBookAuthenticatedNotOwner(): void
    {
        // Arrange
        $user = new User();
        $anotherUser = new User();
        $book = new Book();
        $book->setUser($anotherUser);
        
        /** @var TokenInterface&\PHPUnit\Framework\MockObject\MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // Act
        $result = $this->voter->vote($token, $book, [BookVoter::EDIT]);

        // Assert
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testEditBookNotAuthenticated(): void
    {
        // Arrange
        $book = new Book();
        
        /** @var TokenInterface&\PHPUnit\Framework\MockObject\MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        // Act
        $result = $this->voter->vote($token, $book, [BookVoter::EDIT]);

        // Assert
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    

    public function testDeleteBookNotAuthenticated(): void
    {
        // Arrange
        $book = new Book();
        
        /** @var TokenInterface&\PHPUnit\Framework\MockObject\MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        // Act
        $result = $this->voter->vote($token, $book, [BookVoter::DELETE]);

        // Assert
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testDeleteBookAuthenticatedNotOwner(): void
    {
        // Arrange
        $user = new User();
        $anotherUser = new User();
        $book = new Book();
        $book->setUser($anotherUser);
        
        /** @var TokenInterface&\PHPUnit\Framework\MockObject\MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // Act
        $result = $this->voter->vote($token, $book, [BookVoter::DELETE]);

        // Assert
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testDeleteBookAuthenticatedOwner(): void
    {
        // Arrange
        $user = new User();
        $book = new Book();
        $book->setUser($user);
        
        /** @var TokenInterface&\PHPUnit\Framework\MockObject\MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // Act
        $result = $this->voter->vote($token, $book, [BookVoter::DELETE]);

        // Assert
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    
}