<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Create Super Admin User
        $superAdmin = new User();
        $superAdmin->setUsername('superadmin');
        $superAdmin->setEmail('superadmin@example.com');
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, 'SuperAdmin123!'));
        $superAdmin->setIsActive(true);
        $superAdmin->addRole($this->getReference(RoleFixtures::ROLE_SUPER_ADMIN_REFERENCE, Role::class));
        $manager->persist($superAdmin);

        // Create Admin User
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $admin->setIsActive(true);
        $admin->addRole($this->getReference(RoleFixtures::ROLE_ADMIN_REFERENCE, Role::class));
        $manager->persist($admin);

        // Create Moderator User
        $moderator = new User();
        $moderator->setUsername('moderator');
        $moderator->setEmail('moderator@example.com');
        $moderator->setPassword($this->passwordHasher->hashPassword($moderator, 'Moderator123!'));
        $moderator->setIsActive(true);
        $moderator->addRole($this->getReference(RoleFixtures::ROLE_MODERATOR_REFERENCE, Role::class));
        $manager->persist($moderator);

        // Create Regular Users
        $user1 = new User();
        $user1->setUsername('johndoe');
        $user1->setEmail('john.doe@example.com');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'User123!'));
        $user1->setIsActive(true);
        $user1->addRole($this->getReference(RoleFixtures::ROLE_USER_REFERENCE, Role::class));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('janedoe');
        $user2->setEmail('jane.doe@example.com');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'User123!'));
        $user2->setIsActive(true);
        $user2->addRole($this->getReference(RoleFixtures::ROLE_USER_REFERENCE, Role::class));
        $manager->persist($user2);

        // Create user with multiple roles (admin + moderator)
        $multiRoleUser = new User();
        $multiRoleUser->setUsername('poweruser');
        $multiRoleUser->setEmail('power.user@example.com');
        $multiRoleUser->setPassword($this->passwordHasher->hashPassword($multiRoleUser, 'Power123!'));
        $multiRoleUser->setIsActive(true);
        $multiRoleUser->addRole($this->getReference(RoleFixtures::ROLE_ADMIN_REFERENCE, Role::class));
        $multiRoleUser->addRole($this->getReference(RoleFixtures::ROLE_MODERATOR_REFERENCE, Role::class));
        $manager->persist($multiRoleUser);

        // Create inactive user
        $inactiveUser = new User();
        $inactiveUser->setUsername('inactiveuser');
        $inactiveUser->setEmail('inactive@example.com');
        $inactiveUser->setPassword($this->passwordHasher->hashPassword($inactiveUser, 'Inactive123!'));
        $inactiveUser->setIsActive(false);
        $inactiveUser->addRole($this->getReference(RoleFixtures::ROLE_USER_REFERENCE, Role::class));
        $manager->persist($inactiveUser);

        // Create additional regular users
        $user3 = new User();
        $user3->setUsername('alice');
        $user3->setEmail('alice@example.com');
        $user3->setPassword($this->passwordHasher->hashPassword($user3, 'Alice123!'));
        $user3->setIsActive(true);
        $user3->addRole($this->getReference(RoleFixtures::ROLE_USER_REFERENCE, Role::class));
        $manager->persist($user3);

        $user4 = new User();
        $user4->setUsername('bob');
        $user4->setEmail('bob@example.com');
        $user4->setPassword($this->passwordHasher->hashPassword($user4, 'Bob123!'));
        $user4->setIsActive(true);
        $user4->addRole($this->getReference(RoleFixtures::ROLE_USER_REFERENCE, Role::class));
        $manager->persist($user4);

        $user5 = new User();
        $user5->setUsername('charlie');
        $user5->setEmail('charlie@example.com');
        $user5->setPassword($this->passwordHasher->hashPassword($user5, 'Charlie123!'));
        $user5->setIsActive(true);
        $user5->addRole($this->getReference(RoleFixtures::ROLE_USER_REFERENCE, Role::class));
        $manager->persist($user5);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }
}
