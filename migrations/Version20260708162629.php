<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708162629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE priceshape_vendors_prices DROP FOREIGN KEY `priceshape_vendors_prices_priceshape_vendors_FK`');
        $this->addSql('DROP TABLE google_info');
        $this->addSql('DROP TABLE priceshape_pack_rules');
        $this->addSql('DROP TABLE priceshape_price_changes');
        $this->addSql('DROP TABLE priceshape_price_replication');
        $this->addSql('DROP TABLE priceshape_product_fixed_price');
        $this->addSql('DROP TABLE priceshape_product_info');
        $this->addSql('DROP TABLE priceshape_product_tags');
        $this->addSql('DROP TABLE priceshape_vendors');
        $this->addSql('DROP TABLE priceshape_vendors_prices');
        $this->addSql('ALTER TABLE kpy_boske_fulfillment_cost DROP FOREIGN KEY `FK_75965D585080ECDE`');
        $this->addSql('DROP INDEX IDX_75965D585080ECDE ON kpy_boske_fulfillment_cost');
        $this->addSql('ALTER TABLE kpy_boske_fulfillment_cost DROP warehouse_id');
        $this->addSql('ALTER TABLE kpy_warehouse ADD boske_fulfillment_cost_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE kpy_warehouse ADD CONSTRAINT FK_5CE8C6A14442C0E FOREIGN KEY (boske_fulfillment_cost_id) REFERENCES kpy_warehouse (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5CE8C6A14442C0E ON kpy_warehouse (boske_fulfillment_cost_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE google_info (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, country CHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, ranking INT UNSIGNED DEFAULT NULL, suggested_price FLOAT DEFAULT NULL, potential_click_increase FLOAT DEFAULT NULL, potential_conversion_increase FLOAT DEFAULT NULL, potential_efficiency ENUM(\'low\', \'medium\', \'high\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, date_update DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY (id_product, id_product_attribute, country)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE priceshape_pack_rules (id_pack_rule BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, id_manufacturer INT NOT NULL, product_type INT DEFAULT NULL, discount_percentage FLOAT DEFAULT \'0\' NOT NULL, manual_control TINYINT DEFAULT 0, PRIMARY KEY (id_pack_rule)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE priceshape_price_changes (id_price_change BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, new_price FLOAT DEFAULT NULL, previous_price FLOAT DEFAULT NULL, applied_strategy VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, country CHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, change_date DATETIME DEFAULT CURRENT_TIMESTAMP, execution_id BIGINT UNSIGNED DEFAULT NULL, INDEX priceshape_price_changes_execution_id_IDX (execution_id), INDEX priceshape_price_changes_id_product_IDX (id_product, id_product_attribute, country), PRIMARY KEY (id_price_change)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE priceshape_price_replication (id_price_replication BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, new_price FLOAT DEFAULT NULL, leading_price FLOAT DEFAULT NULL, strategy VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, vendor SMALLINT UNSIGNED DEFAULT NULL, execution_id INT UNSIGNED DEFAULT NULL, change_date DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX priceshape_price_replication_execution_id_IDX (execution_id), PRIMARY KEY (id_price_replication)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE priceshape_product_fixed_price (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY (id_product, id_product_attribute)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE priceshape_product_info (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, global_position SMALLINT UNSIGNED NOT NULL, date_update DATETIME NOT NULL, country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, average_price FLOAT DEFAULT NULL, range_position TINYINT DEFAULT NULL, matches TINYINT DEFAULT NULL, PRIMARY KEY (id_product, id_product_attribute, country)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE priceshape_product_tags (id_priceshape_product_tag BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, id_product INT UNSIGNED DEFAULT NULL, id_product_attribute INT UNSIGNED DEFAULT NULL, country CHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, tag VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, INDEX priceshape_product_tags_id_product_IDX (id_product, id_product_attribute, country), INDEX priceshape_product_tags_tag_IDX (tag), PRIMARY KEY (id_priceshape_product_tag)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE priceshape_vendors (id_vendor INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, domain VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, short_name VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, country CHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, `rank` TINYINT DEFAULT 99, UNIQUE INDEX priceshape_vendors_unique (domain, country), PRIMARY KEY (id_vendor)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE priceshape_vendors_prices (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, id_vendor INT UNSIGNED NOT NULL, price FLOAT NOT NULL, previous_price FLOAT DEFAULT NULL, price_position SMALLINT UNSIGNED DEFAULT NULL, date_price DATETIME DEFAULT NULL, INDEX priceshape_vendors_prices_priceshape_vendors_FK (id_vendor), PRIMARY KEY (id_product, id_product_attribute, id_vendor)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE priceshape_vendors_prices ADD CONSTRAINT `priceshape_vendors_prices_priceshape_vendors_FK` FOREIGN KEY (id_vendor) REFERENCES priceshape_vendors (id_vendor) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kpy_boske_fulfillment_cost ADD warehouse_id INT NOT NULL');
        $this->addSql('ALTER TABLE kpy_boske_fulfillment_cost ADD CONSTRAINT `FK_75965D585080ECDE` FOREIGN KEY (warehouse_id) REFERENCES kpy_warehouse (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_75965D585080ECDE ON kpy_boske_fulfillment_cost (warehouse_id)');
        $this->addSql('ALTER TABLE kpy_warehouse DROP FOREIGN KEY FK_5CE8C6A14442C0E');
        $this->addSql('DROP INDEX UNIQ_5CE8C6A14442C0E ON kpy_warehouse');
        $this->addSql('ALTER TABLE kpy_warehouse DROP boske_fulfillment_cost_id');
    }
}
