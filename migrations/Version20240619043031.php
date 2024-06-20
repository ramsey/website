<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240619043031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track the creator and updater of short URLs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE short_url ADD created_by_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE short_url ADD updated_by_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE short_url ADD CONSTRAINT FK_83360531B03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE short_url ADD CONSTRAINT FK_83360531896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_83360531B03A8386 ON short_url (created_by_id)');
        $this->addSql('CREATE INDEX IDX_83360531896DBBDE ON short_url (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE short_url DROP CONSTRAINT FK_83360531B03A8386');
        $this->addSql('ALTER TABLE short_url DROP CONSTRAINT FK_83360531896DBBDE');
        $this->addSql('DROP INDEX IDX_83360531B03A8386');
        $this->addSql('DROP INDEX IDX_83360531896DBBDE');
        $this->addSql('ALTER TABLE short_url DROP created_by_id');
        $this->addSql('ALTER TABLE short_url DROP updated_by_id');
    }
}
