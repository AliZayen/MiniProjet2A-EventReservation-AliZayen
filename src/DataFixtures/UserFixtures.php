<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setFullName('Admin User');
        $admin->setEmail('admin@example.com');
        $admin->setType(User::TYPE_ADMIN);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $organizer = new User();
        $organizer->setFullName('Organizer User');
        $organizer->setEmail('organizer@example.com');
        $organizer->setType(User::TYPE_ORGANIZER);
        $organizer->setOrganizerApproved(true);
        $organizer->setPassword($this->hasher->hashPassword($organizer, 'organizer123'));
        $manager->persist($organizer);

        $participant = new User();
        $participant->setFullName('Participant User');
        $participant->setEmail('participant@example.com');
        $participant->setType(User::TYPE_PARTICIPANT);
        $participant->setPassword($this->hasher->hashPassword($participant, 'participant123'));
        $manager->persist($participant);

        $manager->flush();
    }
}

