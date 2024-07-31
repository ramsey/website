<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240729015546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes Blamable trait and makes updatedAt nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP CONSTRAINT fk_5a8a6c8db03a8386');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT fk_5a8a6c8d896dbbde');
        $this->addSql('DROP INDEX idx_5a8a6c8d896dbbde');
        $this->addSql('DROP INDEX idx_5a8a6c8db03a8386');
        $this->addSql('ALTER TABLE post DROP created_by_id');
        $this->addSql('ALTER TABLE post DROP updated_by_id');
        $this->addSql('ALTER TABLE post ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE post ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE post_tag ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE post_tag ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE short_url DROP CONSTRAINT fk_83360531b03a8386');
        $this->addSql('ALTER TABLE short_url DROP CONSTRAINT fk_83360531896dbbde');
        $this->addSql('DROP INDEX idx_83360531896dbbde');
        $this->addSql('DROP INDEX idx_83360531b03a8386');
        $this->addSql('ALTER TABLE short_url DROP created_by_id');
        $this->addSql('ALTER TABLE short_url DROP updated_by_id');
        $this->addSql('ALTER TABLE short_url ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE short_url ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER updated_at DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD created_by_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD updated_by_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE post ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE post ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_5a8a6c8db03a8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_5a8a6c8d896dbbde FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5a8a6c8d896dbbde ON post (updated_by_id)');
        $this->addSql('CREATE INDEX idx_5a8a6c8db03a8386 ON post (created_by_id)');
        $this->addSql('ALTER TABLE post_tag ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE post_tag ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE short_url ADD created_by_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE short_url ADD updated_by_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE short_url ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE short_url ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE short_url ADD CONSTRAINT fk_83360531b03a8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE short_url ADD CONSTRAINT fk_83360531896dbbde FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_83360531896dbbde ON short_url (updated_by_id)');
        $this->addSql('CREATE INDEX idx_83360531b03a8386 ON short_url (created_by_id)');
        $this->addSql('ALTER TABLE "user" ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER updated_at SET NOT NULL');
    }
}
