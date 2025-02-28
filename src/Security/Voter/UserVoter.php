<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security as CoreSecurity;

class UserVoter extends Voter
{
    public const DELETE = 'USER_DELETE';
    public const EDIT = 'USER_EDIT';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var User $subject */
        switch ($attribute) {
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
        }

        return false;
    }

    private function canDelete(User $subject, User $user): bool
    {
        // Users can only delete their own account
        return $user === $subject;
    }

    private function canEdit(User $subject, User $user): bool
    {
        // Users can only edit their own account
        return $user === $subject;
    }
}