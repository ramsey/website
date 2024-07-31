<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240731040031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a status property to post entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD status VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP status');
    }
}
