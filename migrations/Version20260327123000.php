<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Tag entity and Event-Tag many-to-many relation, migrate existing event.tags values.';
    }

    public function up(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tag'])) {
            $this->connection->executeStatement('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if (!$schemaManager->tablesExist(['event_tag'])) {
            $this->connection->executeStatement('CREATE TABLE event_tag (event_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_8A3B8A4A71F7E88B (event_id), INDEX IDX_8A3B8A4ABAD26311 (tag_id), PRIMARY KEY(event_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->connection->executeStatement('ALTER TABLE event_tag ADD CONSTRAINT FK_8A3B8A4A71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
            $this->connection->executeStatement('ALTER TABLE event_tag ADD CONSTRAINT FK_8A3B8A4ABAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        }

        $schemaManager = $this->connection->createSchemaManager();
        $eventColumns = $schemaManager->listTableColumns('event');
        if (isset($eventColumns['tags'])) {
            $rows = $this->connection->fetchAllAssociative('SELECT id, tags FROM event WHERE tags IS NOT NULL AND tags <> \'\'');
            foreach ($rows as $row) {
                $eventId = (int) $row['id'];
                $parts = array_filter(array_map('trim', explode(',', (string) $row['tags'])));

                foreach ($parts as $name) {
                    $tagId = $this->connection->fetchOne('SELECT id FROM tag WHERE name = ?', [$name]);
                    if ($tagId === false) {
                        $this->connection->insert('tag', ['name' => $name]);
                        $tagId = $this->connection->lastInsertId();
                    }

                    $exists = $this->connection->fetchOne(
                        'SELECT 1 FROM event_tag WHERE event_id = ? AND tag_id = ?',
                        [$eventId, (int) $tagId]
                    );

                    if ($exists === false) {
                        $this->connection->insert('event_tag', [
                            'event_id' => $eventId,
                            'tag_id' => (int) $tagId,
                        ]);
                    }
                }
            }

            $this->connection->executeStatement('ALTER TABLE event DROP tags');
        }
    }

    public function down(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        $eventColumns = $schemaManager->listTableColumns('event');
        if (!isset($eventColumns['tags'])) {
            $this->connection->executeStatement('ALTER TABLE event ADD tags LONGTEXT DEFAULT NULL');
        }

        if ($schemaManager->tablesExist(['event_tag'])) {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT et.event_id, GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR \', \') AS tag_list
                 FROM event_tag et
                 INNER JOIN tag t ON t.id = et.tag_id
                 GROUP BY et.event_id'
            );

            foreach ($rows as $row) {
                $this->connection->update('event', ['tags' => $row['tag_list']], ['id' => (int) $row['event_id']]);
            }

            $this->connection->executeStatement('DROP TABLE event_tag');
        }

        if ($schemaManager->tablesExist(['tag'])) {
            $this->connection->executeStatement('DROP TABLE tag');
        }
    }
}

