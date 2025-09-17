<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250915091208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE export_temp (id_export_temp SERIAL NOT NULL, id_item_size INT NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id_export_temp))');
        $this->addSql('CREATE INDEX IDX_2DE2B321BFC5DCB6 ON export_temp (id_item_size)');
        $this->addSql('ALTER TABLE export_temp ADD CONSTRAINT FK_2DE2B321BFC5DCB6 FOREIGN KEY (id_item_size) REFERENCES item_size (id_item_size) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE export_temp DROP CONSTRAINT FK_2DE2B321BFC5DCB6');
        $this->addSql('DROP TABLE export_temp');
    }
}
