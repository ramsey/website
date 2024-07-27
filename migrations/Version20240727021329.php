<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240727021329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates entities to support blog posts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE post (id UUID NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, category TEXT NOT NULL, description VARCHAR(255) DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, feed_id VARCHAR(255) DEFAULT NULL, body_type VARCHAR(255) NOT NULL, body TEXT NOT NULL, excerpt TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, author_id UUID DEFAULT NULL, created_by_id UUID DEFAULT NULL, updated_by_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DF675F31B ON post (author_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DB03A8386 ON post (created_by_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D896DBBDE ON post (updated_by_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D8B8E8428989D9B62 ON post (created_at, slug)');
        $this->addSql('CREATE TABLE posts_short_urls (post_id UUID NOT NULL, short_url_id UUID NOT NULL, PRIMARY KEY(post_id, short_url_id))');
        $this->addSql('CREATE INDEX IDX_D863151A4B89032C ON posts_short_urls (post_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D863151AF1252BC8 ON posts_short_urls (short_url_id)');
        $this->addSql('CREATE TABLE posts_post_tags (post_id UUID NOT NULL, post_tag_id UUID NOT NULL, PRIMARY KEY(post_id, post_tag_id))');
        $this->addSql('CREATE INDEX IDX_448B46E14B89032C ON posts_post_tags (post_id)');
        $this->addSql('CREATE INDEX IDX_448B46E18AF08774 ON posts_post_tags (post_tag_id)');
        $this->addSql('CREATE TABLE post_tag (id UUID NOT NULL, name VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5ACE3AF05E237E06 ON post_tag (name)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posts_short_urls ADD CONSTRAINT FK_D863151A4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posts_short_urls ADD CONSTRAINT FK_D863151AF1252BC8 FOREIGN KEY (short_url_id) REFERENCES short_url (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posts_post_tags ADD CONSTRAINT FK_448B46E14B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posts_post_tags ADD CONSTRAINT FK_448B46E18AF08774 FOREIGN KEY (post_tag_id) REFERENCES post_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DF675F31B');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DB03A8386');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D896DBBDE');
        $this->addSql('ALTER TABLE posts_short_urls DROP CONSTRAINT FK_D863151A4B89032C');
        $this->addSql('ALTER TABLE posts_short_urls DROP CONSTRAINT FK_D863151AF1252BC8');
        $this->addSql('ALTER TABLE posts_post_tags DROP CONSTRAINT FK_448B46E14B89032C');
        $this->addSql('ALTER TABLE posts_post_tags DROP CONSTRAINT FK_448B46E18AF08774');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE posts_short_urls');
        $this->addSql('DROP TABLE posts_post_tags');
        $this->addSql('DROP TABLE post_tag');
    }
}
