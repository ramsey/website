<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240601192032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Provide support for short URLs';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE short_url (id UUID NOT NULL, slug VARCHAR(50) NOT NULL, custom_slug VARCHAR(100) DEFAULT NULL, destination_url VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_83360531989D9B62 ON short_url (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8336053199EB539F ON short_url (custom_slug)');
        $this->addSql('CREATE INDEX IDX_83360531E9FF00CC ON short_url (destination_url)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE short_url');
    }
}
