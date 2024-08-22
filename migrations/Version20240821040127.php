<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240821040127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds published_at and modified_at properties';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD published_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD modified_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP published_at');
        $this->addSql('ALTER TABLE post DROP modified_at');
    }
}
