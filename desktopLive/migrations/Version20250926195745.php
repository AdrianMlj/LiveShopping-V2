<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926195745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Color (id_color SERIAL NOT NULL, name_color VARCHAR(100) NOT NULL, PRIMARY KEY(id_color))');
        $this->addSql('CREATE TABLE Goals (id_goal SERIAL NOT NULL, id_seller INT NOT NULL, target_ca NUMERIC(12, 2) NOT NULL, target_ventes INT NOT NULL, PRIMARY KEY(id_goal))');
        $this->addSql('CREATE INDEX IDX_6E5312BDD2D6611 ON Goals (id_seller)');
        $this->addSql('CREATE TABLE Item_size_color (id_item_size_color SERIAL NOT NULL, id_item_size INT NOT NULL, id_color INT NOT NULL, images VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id_item_size_color))');
        $this->addSql('CREATE INDEX IDX_82F29C4ABFC5DCB6 ON Item_size_color (id_item_size)');
        $this->addSql('CREATE INDEX IDX_82F29C4A88D309D9 ON Item_size_color (id_color)');
        $this->addSql('CREATE TABLE commande_details (id_commande_detail SERIAL NOT NULL, id_commande INT NOT NULL, id_item_size INT NOT NULL, quantity INT NOT NULL, price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id_commande_detail))');
        $this->addSql('CREATE INDEX IDX_849D792A3E314AE8 ON commande_details (id_commande)');
        $this->addSql('CREATE INDEX IDX_849D792ABFC5DCB6 ON commande_details (id_item_size)');
        $this->addSql('CREATE TABLE export_temp (id_export_temp SERIAL NOT NULL, id_item_size_color INT NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id_export_temp))');
        $this->addSql('CREATE INDEX IDX_2DE2B321E369615C ON export_temp (id_item_size_color)');
        $this->addSql('CREATE TABLE items_stock (id_item_stock SERIAL NOT NULL, id_item_size_color INT NOT NULL, out_item INT DEFAULT NULL, in_item INT DEFAULT NULL, date_move TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_item_stock))');
        $this->addSql('CREATE INDEX IDX_4FEA9CDBE369615C ON items_stock (id_item_size_color)');
        $this->addSql('ALTER TABLE Goals ADD CONSTRAINT FK_6E5312BDD2D6611 FOREIGN KEY (id_seller) REFERENCES Users (id_user) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE Item_size_color ADD CONSTRAINT FK_82F29C4ABFC5DCB6 FOREIGN KEY (id_item_size) REFERENCES item_size (id_item_size) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE Item_size_color ADD CONSTRAINT FK_82F29C4A88D309D9 FOREIGN KEY (id_color) REFERENCES Color (id_color) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commande_details ADD CONSTRAINT FK_849D792A3E314AE8 FOREIGN KEY (id_commande) REFERENCES commande (id_commande) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commande_details ADD CONSTRAINT FK_849D792ABFC5DCB6 FOREIGN KEY (id_item_size) REFERENCES item_size (id_item_size) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE export_temp ADD CONSTRAINT FK_2DE2B321E369615C FOREIGN KEY (id_item_size_color) REFERENCES Item_size_color (id_item_size_color) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE items_stock ADD CONSTRAINT FK_4FEA9CDBE369615C FOREIGN KEY (id_item_size_color) REFERENCES Item_size_color (id_item_size_color) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT fk_6eeaa67d8586801b');
        $this->addSql('DROP INDEX idx_6eeaa67d8586801b');
        $this->addSql('ALTER TABLE commande ADD id_seller INT NOT NULL');
        $this->addSql('ALTER TABLE commande ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE commande RENAME COLUMN id_bag TO id_client');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DE173B1B8 FOREIGN KEY (id_client) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DDD2D6611 FOREIGN KEY (id_seller) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6EEAA67DE173B1B8 ON commande (id_client)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DDD2D6611 ON commande (id_seller)');
        $this->addSql('ALTER TABLE item ALTER images TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE Goals DROP CONSTRAINT FK_6E5312BDD2D6611');
        $this->addSql('ALTER TABLE Item_size_color DROP CONSTRAINT FK_82F29C4ABFC5DCB6');
        $this->addSql('ALTER TABLE Item_size_color DROP CONSTRAINT FK_82F29C4A88D309D9');
        $this->addSql('ALTER TABLE commande_details DROP CONSTRAINT FK_849D792A3E314AE8');
        $this->addSql('ALTER TABLE commande_details DROP CONSTRAINT FK_849D792ABFC5DCB6');
        $this->addSql('ALTER TABLE export_temp DROP CONSTRAINT FK_2DE2B321E369615C');
        $this->addSql('ALTER TABLE items_stock DROP CONSTRAINT FK_4FEA9CDBE369615C');
        $this->addSql('DROP TABLE Color');
        $this->addSql('DROP TABLE Goals');
        $this->addSql('DROP TABLE Item_size_color');
        $this->addSql('DROP TABLE commande_details');
        $this->addSql('DROP TABLE export_temp');
        $this->addSql('DROP TABLE items_stock');
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
        $this->addSql('ALTER TABLE item ALTER images TYPE VARCHAR(500)');
    }
}
