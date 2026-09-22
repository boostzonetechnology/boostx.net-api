<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add plan display fields for public pricing cards';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plans ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE plans ADD is_highlighted TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE plans ADD display_price VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE plans ADD period_label VARCHAR(32) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plans DROP period_label');
        $this->addSql('ALTER TABLE plans DROP display_price');
        $this->addSql('ALTER TABLE plans DROP is_highlighted');
        $this->addSql('ALTER TABLE plans DROP description');
    }
}
