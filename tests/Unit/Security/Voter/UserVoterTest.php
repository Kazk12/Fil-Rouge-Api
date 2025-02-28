<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserVoterTest extends TestCase
{
    private $voter;
    private $token;

    protected function setUp(): void
    {
        $this->voter = new UserVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function testVoteOnAttributeDeleteOwnAccount(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $this->token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $result = $this->voter->vote($this->token, $user, [UserVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testVoteOnAttributeDeleteOtherAccount(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $otherUser = new User();
        $otherUser->setEmail('otheruser@example.com');

        $this->token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $result = $this->voter->vote($this->token, $otherUser, [UserVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }
}