<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial migration - creates users, roles, and user_role tables
 */
final class Version20251114000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration: creates users, roles, and user_role tables';
    }

    public function up(Schema $schema): void
    {
        // Create roles table
        $this->addSql('CREATE TABLE roles (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(50) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            permissions JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create users table
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            username VARCHAR(180) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username),
            UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create user_role junction table
        $this->addSql('CREATE TABLE user_role (
            user_id INT NOT NULL,
            role_id INT NOT NULL,
            INDEX IDX_2DE8C6A3A76ED395 (user_id),
            INDEX IDX_2DE8C6A3D60322AC (role_id),
            PRIMARY KEY(user_id, role_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC
            FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');

        // Drop tables
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE roles');
    }
}
