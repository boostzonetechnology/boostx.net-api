<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create seo_pages and seo_settings for admin-managed SEO';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE seo_pages (
            id INT AUTO_INCREMENT NOT NULL,
            path VARCHAR(255) NOT NULL,
            title VARCHAR(160) NOT NULL,
            description VARCHAR(500) NOT NULL,
            section VARCHAR(80) NOT NULL,
            is_enabled TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX uniq_seo_pages_path (path),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE seo_settings (
            setting_key VARCHAR(120) NOT NULL,
            setting_value LONGTEXT NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(setting_key)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE seo_pages');
        $this->addSql('DROP TABLE seo_settings');
    }
}
