<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811030219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes the deleted_at column from entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE author DROP deleted_at');
        $this->addSql('ALTER TABLE post DROP deleted_at');
        $this->addSql('ALTER TABLE post_tag DROP deleted_at');
        $this->addSql('ALTER TABLE short_url DROP deleted_at');
        $this->addSql('ALTER TABLE "user" DROP deleted_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post_tag ADD deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE short_url ADD deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE author ADD deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
    }
}
