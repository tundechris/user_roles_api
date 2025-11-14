<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114231902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add refresh_tokens and password_reset_requests tables';
    }

    public function up(Schema $schema): void
    {
        // Create refresh_tokens table
        $this->addSql('CREATE TABLE refresh_tokens (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            is_revoked TINYINT(1) NOT NULL,
            UNIQUE INDEX UNIQ_9BACE7E15F37A13B (token),
            INDEX IDX_9BACE7E1A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE refresh_tokens ADD CONSTRAINT FK_9BACE7E1A76ED395
            FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');

        // Create password_reset_requests table
        $this->addSql('CREATE TABLE password_reset_requests (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            is_used TINYINT(1) NOT NULL,
            UNIQUE INDEX UNIQ_D1D56FA95F37A13B (token),
            INDEX IDX_D1D56FA9A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE password_reset_requests ADD CONSTRAINT FK_D1D56FA9A76ED395
            FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign keys first
        $this->addSql('ALTER TABLE refresh_tokens DROP FOREIGN KEY FK_9BACE7E1A76ED395');
        $this->addSql('ALTER TABLE password_reset_requests DROP FOREIGN KEY FK_D1D56FA9A76ED395');

        // Drop tables
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE password_reset_requests');
    }
}
