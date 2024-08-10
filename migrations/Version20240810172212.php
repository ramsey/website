<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240810172212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create author entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE author (id UUID NOT NULL, byline VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, user_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BDAFD8C8A76ED395 ON author (user_id)');
        $this->addSql('CREATE TABLE posts_authors (post_id UUID NOT NULL, author_id UUID NOT NULL, PRIMARY KEY(post_id, author_id))');
        $this->addSql('CREATE INDEX IDX_216C333A4B89032C ON posts_authors (post_id)');
        $this->addSql('CREATE INDEX IDX_216C333AF675F31B ON posts_authors (author_id)');
        $this->addSql('ALTER TABLE author ADD CONSTRAINT FK_BDAFD8C8A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posts_authors ADD CONSTRAINT FK_216C333A4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posts_authors ADD CONSTRAINT FK_216C333AF675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT fk_5a8a6c8df675f31b');
        $this->addSql('DROP INDEX idx_5a8a6c8df675f31b');
        $this->addSql('ALTER TABLE post DROP author_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE author DROP CONSTRAINT FK_BDAFD8C8A76ED395');
        $this->addSql('ALTER TABLE posts_authors DROP CONSTRAINT FK_216C333A4B89032C');
        $this->addSql('ALTER TABLE posts_authors DROP CONSTRAINT FK_216C333AF675F31B');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE posts_authors');
        $this->addSql('ALTER TABLE post ADD author_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_5a8a6c8df675f31b FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5a8a6c8df675f31b ON post (author_id)');
    }
}
