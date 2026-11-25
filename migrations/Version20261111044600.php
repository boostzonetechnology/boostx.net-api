<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261111044600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create BoostX contact submissions table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE contact_submissions (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                interest VARCHAR(100) NOT NULL,
                message TEXT DEFAULT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'new',
                is_favorite TINYINT(1) NOT NULL DEFAULT 0,
                admin_note LONGTEXT DEFAULT NULL,
                is_deleted TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_contact_status (status),
                INDEX idx_contact_created (created_at),
                INDEX idx_contact_email (email),
                INDEX idx_contact_favorite (is_favorite),
                INDEX idx_contact_deleted (is_deleted)
            ) ENGINE=InnoDB
              DEFAULT CHARACTER SET utf8mb4
              COLLATE utf8mb4_unicode_ci
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_submissions');
    }
}
