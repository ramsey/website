<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240710034644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create analytics event and device entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE analytics_device (id UUID NOT NULL, brand_name VARCHAR(255) DEFAULT NULL, category VARCHAR(255) DEFAULT NULL, device VARCHAR(255) NOT NULL, engine VARCHAR(255) DEFAULT NULL, family VARCHAR(255) DEFAULT NULL, is_bot BOOLEAN NOT NULL, name VARCHAR(255) NOT NULL, os_family VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A1EF17E0794474B964C19C192FB68EE8A81A8DA5E6215B5E237E0686B5 ON analytics_device (brand_name, category, device, engine, family, name, os_family)');
        $this->addSql('CREATE TABLE analytics_event (id UUID NOT NULL, geo_city VARCHAR(255) DEFAULT NULL, geo_country_code VARCHAR(2) DEFAULT NULL, geo_latitude DOUBLE PRECISION DEFAULT NULL, geo_longitude DOUBLE PRECISION DEFAULT NULL, geo_subdivision_code VARCHAR(3) DEFAULT NULL, hostname VARCHAR(50) NOT NULL, ip_user_agent_hash BYTEA NOT NULL, locale VARCHAR(50) DEFAULT NULL, name VARCHAR(255) NOT NULL, redirect_url VARCHAR(255) DEFAULT NULL, referrer VARCHAR(255) DEFAULT NULL, tags JSONB NOT NULL, uri VARCHAR(255) NOT NULL, user_agent VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, device_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9CD0310A94A4C7D4 ON analytics_event (device_id)');
        $this->addSql('CREATE INDEX IDX_9CD0310AE551C0115E237E06 ON analytics_event (hostname, name)');
        $this->addSql('ALTER TABLE analytics_event ADD CONSTRAINT FK_9CD0310A94A4C7D4 FOREIGN KEY (device_id) REFERENCES analytics_device (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE analytics_event DROP CONSTRAINT FK_9CD0310A94A4C7D4');
        $this->addSql('DROP TABLE analytics_device');
        $this->addSql('DROP TABLE analytics_event');
    }
}
