<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create regions and plans tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE regions (
            id INT AUTO_INCREMENT NOT NULL,
            country_name VARCHAR(120) NOT NULL,
            country_code VARCHAR(8) NOT NULL,
            currency_code VARCHAR(8) NOT NULL,
            currency_icon VARCHAR(32) NOT NULL,
            is_enabled TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX uniq_regions_country_code (country_code),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE plans (
            id INT AUTO_INCREMENT NOT NULL,
            region_id INT NOT NULL,
            name VARCHAR(160) NOT NULL,
            slug VARCHAR(180) NOT NULL,
            price NUMERIC(12, 2) NOT NULL,
            billing_period VARCHAR(32) NOT NULL DEFAULT \'monthly\',
            features LONGTEXT DEFAULT NULL,
            is_enabled TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_PLANS_REGION (region_id),
            UNIQUE INDEX uniq_plans_region_slug (region_id, slug),
            PRIMARY KEY(id),
            CONSTRAINT FK_PLANS_REGION FOREIGN KEY (region_id) REFERENCES regions (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plans DROP FOREIGN KEY FK_PLANS_REGION');
        $this->addSql('DROP TABLE plans');
        $this->addSql('DROP TABLE regions');
    }
}
