<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829111346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande_details (id_commande_detail SERIAL NOT NULL, id_commande INT NOT NULL, id_item_size INT NOT NULL, quantity INT NOT NULL, price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id_commande_detail))');
        $this->addSql('CREATE INDEX IDX_849D792A3E314AE8 ON commande_details (id_commande)');
        $this->addSql('CREATE INDEX IDX_849D792ABFC5DCB6 ON commande_details (id_item_size)');
        $this->addSql('ALTER TABLE commande_details ADD CONSTRAINT FK_849D792A3E314AE8 FOREIGN KEY (id_commande) REFERENCES commande (id_commande) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commande_details ADD CONSTRAINT FK_849D792ABFC5DCB6 FOREIGN KEY (id_item_size) REFERENCES item_size (id_item_size) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bag_details ALTER price DROP DEFAULT');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT fk_6eeaa67d8586801b');
        $this->addSql('DROP INDEX idx_6eeaa67d8586801b');
        $this->addSql('ALTER TABLE commande ADD id_seller INT NOT NULL');
        $this->addSql('ALTER TABLE commande ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE commande RENAME COLUMN id_bag TO id_client');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DE173B1B8 FOREIGN KEY (id_client) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DDD2D6611 FOREIGN KEY (id_seller) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6EEAA67DE173B1B8 ON commande (id_client)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DDD2D6611 ON commande (id_seller)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commande_details DROP CONSTRAINT FK_849D792A3E314AE8');
        $this->addSql('ALTER TABLE commande_details DROP CONSTRAINT FK_849D792ABFC5DCB6');
        $this->addSql('DROP TABLE commande_details');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67DE173B1B8');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67DDD2D6611');
        $this->addSql('DROP INDEX IDX_6EEAA67DE173B1B8');
        $this->addSql('DROP INDEX IDX_6EEAA67DDD2D6611');
        $this->addSql('ALTER TABLE commande ADD id_bag INT NOT NULL');
        $this->addSql('ALTER TABLE commande DROP id_client');
        $this->addSql('ALTER TABLE commande DROP id_seller');
        $this->addSql('ALTER TABLE commande DROP created_at');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT fk_6eeaa67d8586801b FOREIGN KEY (id_bag) REFERENCES bag (id_bag) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6eeaa67d8586801b ON commande (id_bag)');
        $this->addSql('ALTER TABLE bag_details ALTER price SET DEFAULT \'0.00\'');
    }
}
