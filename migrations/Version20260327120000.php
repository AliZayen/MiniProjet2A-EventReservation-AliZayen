<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ticket prices, tags, created_at, and updated_at to event table.';
    }

    public function up(Schema $schema): void
    {
        $columns = $this->connection->createSchemaManager()->listTableColumns('event');

        if (!isset($columns['ticket_prices'])) {
            $this->addSql('ALTER TABLE event ADD ticket_prices LONGTEXT DEFAULT NULL');
        }
        if (!isset($columns['tags'])) {
            $this->addSql('ALTER TABLE event ADD tags LONGTEXT DEFAULT NULL');
        }
        if (!isset($columns['created_at'])) {
            $this->addSql('ALTER TABLE event ADD created_at DATETIME DEFAULT NULL');
        }
        if (!isset($columns['updated_at'])) {
            $this->addSql('ALTER TABLE event ADD updated_at DATETIME DEFAULT NULL');
        }

        $this->addSql('UPDATE event SET created_at = NOW() WHERE created_at IS NULL');
        $this->addSql('UPDATE event SET updated_at = NOW() WHERE updated_at IS NULL');
        $this->addSql('ALTER TABLE event CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $columns = $this->connection->createSchemaManager()->listTableColumns('event');
        if (isset($columns['ticket_prices'])) {
            $this->addSql('ALTER TABLE event DROP ticket_prices');
        }
        if (isset($columns['tags'])) {
            $this->addSql('ALTER TABLE event DROP tags');
        }
        if (isset($columns['created_at'])) {
            $this->addSql('ALTER TABLE event DROP created_at');
        }
        if (isset($columns['updated_at'])) {
            $this->addSql('ALTER TABLE event DROP updated_at');
        }
    }
}

