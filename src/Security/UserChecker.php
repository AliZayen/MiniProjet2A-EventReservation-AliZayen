<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getType() === User::TYPE_ORGANIZER && !$user->isOrganizerApproved()) {
            throw new CustomUserMessageAccountStatusException('Your organizer account is pending approval by an administrator.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
