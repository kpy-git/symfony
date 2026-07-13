<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260712165644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE kpy_product (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, is_jirafa TINYINT NOT NULL, is_pack TINYINT NOT NULL, PRIMARY KEY (id_product, id_product_attribute)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE kpy_product_prices (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, sales_price_es NUMERIC(6, 2) NOT NULL, PRIMARY KEY (id_product, id_product_attribute)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, carrier_service VARCHAR(255) NOT NULL, cost_strategy_type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_ECB38BFC5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse_package (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_weight_allowed NUMERIC(5, 2) NOT NULL, cost NUMERIC(5, 2) NOT NULL, weight NUMERIC(5, 2) NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_F98F72E05080ECDE (warehouse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse_product (id INT AUTO_INCREMENT NOT NULL, id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, final_cost_price NUMERIC(10, 6) NOT NULL, is_default TINYINT NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_F4AD11D85080ECDE (warehouse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE warehouse_package ADD CONSTRAINT FK_F98F72E05080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE warehouse_product ADD CONSTRAINT FK_F4AD11D85080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE warehouse_package DROP FOREIGN KEY FK_F98F72E05080ECDE');
        $this->addSql('ALTER TABLE warehouse_product DROP FOREIGN KEY FK_F4AD11D85080ECDE');
        $this->addSql('DROP TABLE kpy_product');
        $this->addSql('DROP TABLE kpy_product_prices');
        $this->addSql('DROP TABLE warehouse');
        $this->addSql('DROP TABLE warehouse_package');
        $this->addSql('DROP TABLE warehouse_product');
    }
}
