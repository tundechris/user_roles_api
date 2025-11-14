<?php
declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Role;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPassword('hashedpassword');

        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('hashedpassword', $user->getPassword());
        $this->assertTrue($user->getIsActive());
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getUpdatedAt());
    }

    public function testUserRoles(): void
    {
        $user = new User();
        $user->setUsername('testuser');

        $role = new Role();
        $role->setName('ROLE_ADMIN');

        $user->addRole($role);

        $this->assertCount(1, $user->getRoleEntities());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles()); // Default role
    }

    public function testRemoveRole(): void
    {
        $user = new User();
        $user->setUsername('testuser');

        $role = new Role();
        $role->setName('ROLE_ADMIN');

        $user->addRole($role);
        $this->assertCount(1, $user->getRoleEntities());

        $user->removeRole($role);
        $this->assertCount(0, $user->getRoleEntities());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setUsername('testuser');

        $this->assertEquals('testuser', $user->getUserIdentifier());
    }
}
