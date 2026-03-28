<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260328023824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_registration (id INT AUTO_INCREMENT NOT NULL, payment_status VARCHAR(30) NOT NULL, reserved_at DATETIME NOT NULL, ticket_type VARCHAR(120) DEFAULT NULL, user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_8FBBAD54A76ED395 (user_id), INDEX IDX_8FBBAD5471F7E88B (event_id), UNIQUE INDEX event_registration_user_event_unique (user_id, event_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event_registration ADD CONSTRAINT FK_8FBBAD54A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_registration ADD CONSTRAINT FK_8FBBAD5471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD phone_number VARCHAR(40) DEFAULT NULL, ADD address LONGTEXT DEFAULT NULL, ADD profession VARCHAR(120) DEFAULT NULL, ADD birth_date DATE DEFAULT NULL, ADD website VARCHAR(255) DEFAULT NULL, ADD facebook VARCHAR(255) DEFAULT NULL, ADD instagram VARCHAR(255) DEFAULT NULL, ADD whatsapp VARCHAR(50) DEFAULT NULL, ADD organizer_approved TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_registration DROP FOREIGN KEY FK_8FBBAD54A76ED395');
        $this->addSql('ALTER TABLE event_registration DROP FOREIGN KEY FK_8FBBAD5471F7E88B');
        $this->addSql('DROP TABLE event_registration');
        $this->addSql('ALTER TABLE `user` DROP phone_number, DROP address, DROP profession, DROP birth_date, DROP website, DROP facebook, DROP instagram, DROP whatsapp, DROP organizer_approved');
    }
}
