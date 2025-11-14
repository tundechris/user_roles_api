<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public const ROLE_USER_REFERENCE = 'role-user';
    public const ROLE_ADMIN_REFERENCE = 'role-admin';
    public const ROLE_SUPER_ADMIN_REFERENCE = 'role-super-admin';
    public const ROLE_MODERATOR_REFERENCE = 'role-moderator';

    public function load(ObjectManager $manager): void
    {
        // Create ROLE_USER
        $roleUser = new Role();
        $roleUser->setName('ROLE_USER');
        $roleUser->setDescription('Standard user with basic access');
        $roleUser->setPermissions(['read:own-profile', 'update:own-profile']);
        $manager->persist($roleUser);
        $this->addReference(self::ROLE_USER_REFERENCE, $roleUser);

        // Create ROLE_MODERATOR
        $roleModerator = new Role();
        $roleModerator->setName('ROLE_MODERATOR');
        $roleModerator->setDescription('Moderator with extended permissions');
        $roleModerator->setPermissions([
            'read:own-profile',
            'update:own-profile',
            'read:users',
            'moderate:content'
        ]);
        $manager->persist($roleModerator);
        $this->addReference(self::ROLE_MODERATOR_REFERENCE, $roleModerator);

        // Create ROLE_ADMIN
        $roleAdmin = new Role();
        $roleAdmin->setName('ROLE_ADMIN');
        $roleAdmin->setDescription('Administrator with full user management access');
        $roleAdmin->setPermissions([
            'read:own-profile',
            'update:own-profile',
            'read:users',
            'create:users',
            'update:users',
            'delete:users',
            'read:roles',
            'assign:roles'
        ]);
        $manager->persist($roleAdmin);
        $this->addReference(self::ROLE_ADMIN_REFERENCE, $roleAdmin);

        // Create ROLE_SUPER_ADMIN
        $roleSuperAdmin = new Role();
        $roleSuperAdmin->setName('ROLE_SUPER_ADMIN');
        $roleSuperAdmin->setDescription('Super administrator with complete system access');
        $roleSuperAdmin->setPermissions([
            'read:*',
            'create:*',
            'update:*',
            'delete:*',
            'manage:system'
        ]);
        $manager->persist($roleSuperAdmin);
        $this->addReference(self::ROLE_SUPER_ADMIN_REFERENCE, $roleSuperAdmin);

        $manager->flush();
    }
}
