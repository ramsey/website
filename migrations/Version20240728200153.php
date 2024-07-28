<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240728200153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds metadata property to the post entity and updates keywords as array';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD metadata JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE post ALTER keywords TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP metadata');
        $this->addSql('ALTER TABLE post ALTER keywords TYPE VARCHAR(255)');
    }
}
