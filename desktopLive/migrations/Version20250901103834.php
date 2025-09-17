<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901103834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Goals (id_goal SERIAL NOT NULL, id_seller INT NOT NULL, target_ca NUMERIC(15, 2) NOT NULL, target_ventes INT NOT NULL, PRIMARY KEY(id_goal))');
        $this->addSql('CREATE INDEX IDX_6E5312BDD2D6611 ON Goals (id_seller)');
        $this->addSql('ALTER TABLE Goals ADD CONSTRAINT FK_6E5312BDD2D6611 FOREIGN KEY (id_seller) REFERENCES Users (id_user) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE Goals DROP CONSTRAINT FK_6E5312BDD2D6611');
        $this->addSql('DROP TABLE Goals');
    }
}
