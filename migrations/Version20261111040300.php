<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261111040300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin favorite, note, and soft-delete columns to join_submissions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE join_submissions ADD is_favorite TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE join_submissions ADD admin_note LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE join_submissions ADD is_deleted TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE join_submissions ADD INDEX idx_join_favorite (is_favorite)');
        $this->addSql('ALTER TABLE join_submissions ADD INDEX idx_join_deleted (is_deleted)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE join_submissions DROP INDEX idx_join_deleted');
        $this->addSql('ALTER TABLE join_submissions DROP INDEX idx_join_favorite');
        $this->addSql('ALTER TABLE join_submissions DROP is_deleted');
        $this->addSql('ALTER TABLE join_submissions DROP admin_note');
        $this->addSql('ALTER TABLE join_submissions DROP is_favorite');
    }
}
