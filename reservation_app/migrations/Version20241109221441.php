<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241109221441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking ADD user_appli_id INT NOT NULL');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEFD487841 FOREIGN KEY (user_appli_id) REFERENCES user_application (id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEFD487841 ON booking (user_appli_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEFD487841');
        $this->addSql('DROP INDEX IDX_E00CEDDEFD487841 ON booking');
        $this->addSql('ALTER TABLE booking DROP user_appli_id');
    }
}
