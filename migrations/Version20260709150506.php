<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709150506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE warehouse (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, carrier_service VARCHAR(255) NOT NULL, cost_strategy_type VARCHAR(255) NOT NULL, packaging_included TINYINT NOT NULL, fixed_cost_for_small_item NUMERIC(4, 2) NOT NULL, boske_fulfillment_cost_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_ECB38BFC14442C0E (boske_fulfillment_cost_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse_boske_fulfillment_cost (id INT AUTO_INCREMENT NOT NULL, single_item_up_to_5kg NUMERIC(5, 2) NOT NULL, single_item_starting_at_5kg NUMERIC(4, 2) NOT NULL, additional_items_up_to_5kg NUMERIC(4, 2) NOT NULL, additional_items_starting_at_5kg NUMERIC(4, 2) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse_product (id INT AUTO_INCREMENT NOT NULL, id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, is_default TINYINT NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_F4AD11D85080ECDE (warehouse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE warehouse ADD CONSTRAINT FK_ECB38BFC14442C0E FOREIGN KEY (boske_fulfillment_cost_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE warehouse_product ADD CONSTRAINT FK_F4AD11D85080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE kpy_boske_fulfillment_cost DROP FOREIGN KEY `FK_75965D585080ECDE`');
        $this->addSql('ALTER TABLE kpy_product DROP FOREIGN KEY `FK_701B6A3F5080ECDE`');
        $this->addSql('DROP INDEX IDX_701B6A3F5080ECDE ON kpy_product');
        $this->addSql('ALTER TABLE kpy_product ADD is_pack TINYINT NOT NULL, DROP warehouse_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE warehouse DROP FOREIGN KEY FK_ECB38BFC14442C0E');
        $this->addSql('ALTER TABLE warehouse_product DROP FOREIGN KEY FK_F4AD11D85080ECDE');
        $this->addSql('DROP TABLE warehouse');
        $this->addSql('DROP TABLE warehouse_boske_fulfillment_cost');
        $this->addSql('DROP TABLE warehouse_product');
        $this->addSql('ALTER TABLE kpy_product ADD warehouse_id INT NOT NULL, DROP is_pack');
        $this->addSql('ALTER TABLE kpy_product ADD CONSTRAINT `FK_701B6A3F5080ECDE` FOREIGN KEY (warehouse_id) REFERENCES kpy_warehouse (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_701B6A3F5080ECDE ON kpy_product (warehouse_id)');
    }
}
