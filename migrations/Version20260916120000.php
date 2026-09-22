<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create admins, admin_tokens, blog_categories, blogs tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE admins (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX uniq_admins_email (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE admin_tokens (
            token VARCHAR(64) NOT NULL,
            admin_id INT NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_ADMIN_TOKENS_ADMIN (admin_id),
            PRIMARY KEY(token),
            CONSTRAINT FK_ADMIN_TOKENS_ADMIN FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE blog_categories (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX uniq_blog_categories_slug (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE blogs (
            id INT AUTO_INCREMENT NOT NULL,
            category_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            excerpt LONGTEXT DEFAULT NULL,
            featured_image VARCHAR(500) DEFAULT NULL,
            author VARCHAR(120) NOT NULL,
            post_at DATETIME NOT NULL,
            tags VARCHAR(255) NOT NULL,
            seo_title VARCHAR(255) DEFAULT NULL,
            seo_description VARCHAR(320) DEFAULT NULL,
            published TINYINT(1) NOT NULL,
            lang VARCHAR(8) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX uniq_blogs_slug (slug),
            INDEX IDX_BLOGS_CATEGORY (category_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_BLOGS_CATEGORY FOREIGN KEY (category_id) REFERENCES blog_categories (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blogs DROP FOREIGN KEY FK_BLOGS_CATEGORY');
        $this->addSql('ALTER TABLE admin_tokens DROP FOREIGN KEY FK_ADMIN_TOKENS_ADMIN');
        $this->addSql('DROP TABLE blogs');
        $this->addSql('DROP TABLE blog_categories');
        $this->addSql('DROP TABLE admin_tokens');
        $this->addSql('DROP TABLE admins');
    }
}
