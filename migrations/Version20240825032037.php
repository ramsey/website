<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240825032037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds support for adding links to authors';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE author_link (id UUID NOT NULL, type VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, author_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D243112EF675F31B ON author_link (author_id)');
        $this->addSql('ALTER TABLE author_link ADD CONSTRAINT FK_D243112EF675F31B FOREIGN KEY (author_id) REFERENCES author (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE author_link DROP CONSTRAINT FK_D243112EF675F31B');
        $this->addSql('DROP TABLE author_link');
    }
}
