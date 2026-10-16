<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015173229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create BoostX join submissions table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE join_submissions (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                company VARCHAR(200) DEFAULT NULL,
                country VARCHAR(100) DEFAULT NULL,
                membership_type VARCHAR(100) NOT NULL,
                message TEXT DEFAULT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'new',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_join_status (status),
                INDEX idx_join_created (created_at),
                INDEX idx_join_email (email)
            ) ENGINE=InnoDB
              DEFAULT CHARACTER SET utf8mb4
              COLLATE utf8mb4_unicode_ci
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE join_submissions');
    }
}