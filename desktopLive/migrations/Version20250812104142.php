<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812104142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Users (id_user SERIAL NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(500) NOT NULL, contact VARCHAR(10) NOT NULL, address VARCHAR(255) NOT NULL, country VARCHAR(500) NOT NULL, images TEXT DEFAULT NULL, is_seller BOOLEAN NOT NULL, PRIMARY KEY(id_user))');
        $this->addSql('CREATE TABLE bag (id_bag SERIAL NOT NULL, id_client INT NOT NULL, id_seller INT NOT NULL, create_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_commande BOOLEAN DEFAULT NULL, PRIMARY KEY(id_bag))');
        $this->addSql('CREATE INDEX IDX_1B226841E173B1B8 ON bag (id_client)');
        $this->addSql('CREATE INDEX IDX_1B226841DD2D6611 ON bag (id_seller)');
        $this->addSql('CREATE TABLE bag_details (id_bag_detail SERIAL NOT NULL, id_item_size INT NOT NULL, id_bag INT NOT NULL, price NUMERIC(15, 2) NOT NULL, PRIMARY KEY(id_bag_detail))');
        $this->addSql('CREATE INDEX IDX_65428A8BFC5DCB6 ON bag_details (id_item_size)');
        $this->addSql('CREATE INDEX IDX_65428A88586801B ON bag_details (id_bag)');
        $this->addSql('CREATE TABLE category (id_category SERIAL NOT NULL, name_category VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id_category))');
        $this->addSql('CREATE TABLE commande (id_commande SERIAL NOT NULL, id_state INT NOT NULL, id_bag INT NOT NULL, PRIMARY KEY(id_commande))');
        $this->addSql('CREATE INDEX IDX_6EEAA67D4D1693CB ON commande (id_state)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D8586801B ON commande (id_bag)');
        $this->addSql('CREATE TABLE favorite_details (id_favorite_detail SERIAL NOT NULL, id_item_size INT NOT NULL, id_favorites INT NOT NULL, PRIMARY KEY(id_favorite_detail))');
        $this->addSql('CREATE INDEX IDX_2F72A5E4BFC5DCB6 ON favorite_details (id_item_size)');
        $this->addSql('CREATE INDEX IDX_2F72A5E4645CDCD2 ON favorite_details (id_favorites)');
        $this->addSql('CREATE TABLE favorites (id_favorites SERIAL NOT NULL, id_client INT NOT NULL, create_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_favorites))');
        $this->addSql('CREATE INDEX IDX_E46960F5E173B1B8 ON favorites (id_client)');
        $this->addSql('CREATE TABLE follow_seller (id_follow SERIAL NOT NULL, id_client INT NOT NULL, id_seller INT NOT NULL, date_following TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_follow))');
        $this->addSql('CREATE INDEX IDX_D0BB6D84E173B1B8 ON follow_seller (id_client)');
        $this->addSql('CREATE INDEX IDX_D0BB6D84DD2D6611 ON follow_seller (id_seller)');
        $this->addSql('CREATE TABLE item (id_item SERIAL NOT NULL, id_seller INT NOT NULL, id_category INT NOT NULL, images VARCHAR(500) DEFAULT NULL, name_item VARCHAR(255) NOT NULL, PRIMARY KEY(id_item))');
        $this->addSql('CREATE INDEX IDX_1F1B251EDD2D6611 ON item (id_seller)');
        $this->addSql('CREATE INDEX IDX_1F1B251E5697F554 ON item (id_category)');
        $this->addSql('CREATE TABLE item_size (id_item_size SERIAL NOT NULL, id_size INT NOT NULL, id_item INT NOT NULL, value_size VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id_item_size))');
        $this->addSql('CREATE INDEX IDX_3FF060917CE03868 ON item_size (id_size)');
        $this->addSql('CREATE INDEX IDX_3FF06091943B391C ON item_size (id_item)');
        $this->addSql('CREATE TABLE items_stock (id_item_stock SERIAL NOT NULL, id_item_size INT NOT NULL, out_item INT DEFAULT NULL, in_item INT DEFAULT NULL, date_move TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_item_stock))');
        $this->addSql('CREATE INDEX IDX_4FEA9CDBBFC5DCB6 ON items_stock (id_item_size)');
        $this->addSql('CREATE TABLE liaison_notification (id_liaison SERIAL NOT NULL, id_notification INT NOT NULL, name_table VARCHAR(50) NOT NULL, id_table INT NOT NULL, PRIMARY KEY(id_liaison))');
        $this->addSql('CREATE INDEX IDX_11C7F5259C9503B8 ON liaison_notification (id_notification)');
        $this->addSql('CREATE TABLE live (id_live SERIAL NOT NULL, id_seller INT NOT NULL, start_live TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_live TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, nbr_like INT DEFAULT NULL, titre VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id_live))');
        $this->addSql('CREATE INDEX IDX_530F2CAFDD2D6611 ON live (id_seller)');
        $this->addSql('CREATE TABLE live_details (id_live_detail SERIAL NOT NULL, id_item INT NOT NULL, id_live INT NOT NULL, PRIMARY KEY(id_live_detail))');
        $this->addSql('CREATE INDEX IDX_F3FB4BF1943B391C ON live_details (id_item)');
        $this->addSql('CREATE INDEX IDX_F3FB4BF1D82F30AD ON live_details (id_live)');
        $this->addSql('CREATE TABLE notification (id_notification SERIAL NOT NULL, id_type INT NOT NULL, id_user INT NOT NULL, title VARCHAR(500) NOT NULL, content TEXT NOT NULL, is_read BOOLEAN DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_notification))');
        $this->addSql('CREATE INDEX IDX_BF5476CA7FE4B2B ON notification (id_type)');
        $this->addSql('CREATE INDEX IDX_BF5476CA6B3CA4B ON notification (id_user)');
        $this->addSql('CREATE TABLE notification_type (id_type SERIAL NOT NULL, name_type VARCHAR(255) NOT NULL, PRIMARY KEY(id_type))');
        $this->addSql('CREATE TABLE price_items (id_price SERIAL NOT NULL, id_item INT NOT NULL, price NUMERIC(15, 2) NOT NULL, date_price DATE NOT NULL, PRIMARY KEY(id_price))');
        $this->addSql('CREATE INDEX IDX_CAD7E12D943B391C ON price_items (id_item)');
        $this->addSql('CREATE TABLE promotion (id_promotion SERIAL NOT NULL, id_item INT NOT NULL, name_promotion VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, percentage NUMERIC(15, 2) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, PRIMARY KEY(id_promotion))');
        $this->addSql('CREATE INDEX IDX_C11D7DD1943B391C ON promotion (id_item)');
        $this->addSql('CREATE TABLE sale (id_sale SERIAL NOT NULL, id_commande INT NOT NULL, sale_date DATE NOT NULL, is_paid BOOLEAN NOT NULL, PRIMARY KEY(id_sale))');
        $this->addSql('CREATE INDEX IDX_E54BC0053E314AE8 ON sale (id_commande)');
        $this->addSql('CREATE TABLE size (id_size SERIAL NOT NULL, name_size VARCHAR(255) NOT NULL, PRIMARY KEY(id_size))');
        $this->addSql('CREATE TABLE state_commande (id_state SERIAL NOT NULL, name_state VARCHAR(255) NOT NULL, PRIMARY KEY(id_state))');
        $this->addSql('ALTER TABLE bag ADD CONSTRAINT FK_1B226841E173B1B8 FOREIGN KEY (id_client) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bag ADD CONSTRAINT FK_1B226841DD2D6611 FOREIGN KEY (id_seller) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bag_details ADD CONSTRAINT FK_65428A8BFC5DCB6 FOREIGN KEY (id_item_size) REFERENCES item_size (id_item_size) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bag_details ADD CONSTRAINT FK_65428A88586801B FOREIGN KEY (id_bag) REFERENCES bag (id_bag) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D4D1693CB FOREIGN KEY (id_state) REFERENCES state_commande (id_state) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D8586801B FOREIGN KEY (id_bag) REFERENCES bag (id_bag) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite_details ADD CONSTRAINT FK_2F72A5E4BFC5DCB6 FOREIGN KEY (id_item_size) REFERENCES item_size (id_item_size) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite_details ADD CONSTRAINT FK_2F72A5E4645CDCD2 FOREIGN KEY (id_favorites) REFERENCES favorites (id_favorites) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5E173B1B8 FOREIGN KEY (id_client) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follow_seller ADD CONSTRAINT FK_D0BB6D84E173B1B8 FOREIGN KEY (id_client) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follow_seller ADD CONSTRAINT FK_D0BB6D84DD2D6611 FOREIGN KEY (id_seller) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EDD2D6611 FOREIGN KEY (id_seller) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E5697F554 FOREIGN KEY (id_category) REFERENCES category (id_category) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item_size ADD CONSTRAINT FK_3FF060917CE03868 FOREIGN KEY (id_size) REFERENCES size (id_size) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item_size ADD CONSTRAINT FK_3FF06091943B391C FOREIGN KEY (id_item) REFERENCES item (id_item) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE items_stock ADD CONSTRAINT FK_4FEA9CDBBFC5DCB6 FOREIGN KEY (id_item_size) REFERENCES item_size (id_item_size) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE liaison_notification ADD CONSTRAINT FK_11C7F5259C9503B8 FOREIGN KEY (id_notification) REFERENCES notification (id_notification) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE live ADD CONSTRAINT FK_530F2CAFDD2D6611 FOREIGN KEY (id_seller) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE live_details ADD CONSTRAINT FK_F3FB4BF1943B391C FOREIGN KEY (id_item) REFERENCES item (id_item) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE live_details ADD CONSTRAINT FK_F3FB4BF1D82F30AD FOREIGN KEY (id_live) REFERENCES live (id_live) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7FE4B2B FOREIGN KEY (id_type) REFERENCES notification_type (id_type) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA6B3CA4B FOREIGN KEY (id_user) REFERENCES Users (id_user) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE price_items ADD CONSTRAINT FK_CAD7E12D943B391C FOREIGN KEY (id_item) REFERENCES item (id_item) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE promotion ADD CONSTRAINT FK_C11D7DD1943B391C FOREIGN KEY (id_item) REFERENCES item (id_item) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC0053E314AE8 FOREIGN KEY (id_commande) REFERENCES commande (id_commande) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bag DROP CONSTRAINT FK_1B226841E173B1B8');
        $this->addSql('ALTER TABLE bag DROP CONSTRAINT FK_1B226841DD2D6611');
        $this->addSql('ALTER TABLE bag_details DROP CONSTRAINT FK_65428A8BFC5DCB6');
        $this->addSql('ALTER TABLE bag_details DROP CONSTRAINT FK_65428A88586801B');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67D4D1693CB');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67D8586801B');
        $this->addSql('ALTER TABLE favorite_details DROP CONSTRAINT FK_2F72A5E4BFC5DCB6');
        $this->addSql('ALTER TABLE favorite_details DROP CONSTRAINT FK_2F72A5E4645CDCD2');
        $this->addSql('ALTER TABLE favorites DROP CONSTRAINT FK_E46960F5E173B1B8');
        $this->addSql('ALTER TABLE follow_seller DROP CONSTRAINT FK_D0BB6D84E173B1B8');
        $this->addSql('ALTER TABLE follow_seller DROP CONSTRAINT FK_D0BB6D84DD2D6611');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251EDD2D6611');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E5697F554');
        $this->addSql('ALTER TABLE item_size DROP CONSTRAINT FK_3FF060917CE03868');
        $this->addSql('ALTER TABLE item_size DROP CONSTRAINT FK_3FF06091943B391C');
        $this->addSql('ALTER TABLE items_stock DROP CONSTRAINT FK_4FEA9CDBBFC5DCB6');
        $this->addSql('ALTER TABLE liaison_notification DROP CONSTRAINT FK_11C7F5259C9503B8');
        $this->addSql('ALTER TABLE live DROP CONSTRAINT FK_530F2CAFDD2D6611');
        $this->addSql('ALTER TABLE live_details DROP CONSTRAINT FK_F3FB4BF1943B391C');
        $this->addSql('ALTER TABLE live_details DROP CONSTRAINT FK_F3FB4BF1D82F30AD');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA7FE4B2B');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA6B3CA4B');
        $this->addSql('ALTER TABLE price_items DROP CONSTRAINT FK_CAD7E12D943B391C');
        $this->addSql('ALTER TABLE promotion DROP CONSTRAINT FK_C11D7DD1943B391C');
        $this->addSql('ALTER TABLE sale DROP CONSTRAINT FK_E54BC0053E314AE8');
        $this->addSql('DROP TABLE Users');
        $this->addSql('DROP TABLE bag');
        $this->addSql('DROP TABLE bag_details');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE favorite_details');
        $this->addSql('DROP TABLE favorites');
        $this->addSql('DROP TABLE follow_seller');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE item_size');
        $this->addSql('DROP TABLE items_stock');
        $this->addSql('DROP TABLE liaison_notification');
        $this->addSql('DROP TABLE live');
        $this->addSql('DROP TABLE live_details');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE notification_type');
        $this->addSql('DROP TABLE price_items');
        $this->addSql('DROP TABLE promotion');
        $this->addSql('DROP TABLE sale');
        $this->addSql('DROP TABLE size');
        $this->addSql('DROP TABLE state_commande');
    }
}
