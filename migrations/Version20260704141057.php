<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260704141057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE kpy_boske_fulfillment_cost (id INT AUTO_INCREMENT NOT NULL, single_item_up_to_5kg NUMERIC(5, 2) NOT NULL, single_item_starting_at_5kg NUMERIC(4, 2) NOT NULL, additional_items_up_to_5kg NUMERIC(4, 2) NOT NULL, additional_items_starting_at_5kg NUMERIC(4, 2) NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_75965D585080ECDE (warehouse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE kpy_product (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, is_jirafa TINYINT NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_701B6A3F5080ECDE (warehouse_id), PRIMARY KEY (id_product, id_product_attribute)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE kpy_product_prices (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, sales_price_es NUMERIC(6, 2) NOT NULL, final_cost_price NUMERIC(10, 6) NOT NULL, pcmp NUMERIC(10, 6) NOT NULL, PRIMARY KEY (id_product, id_product_attribute)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE kpy_warehouse (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, carrier_service VARCHAR(255) NOT NULL, commission NUMERIC(4, 2) NOT NULL, packaging_included TINYINT NOT NULL, fixed_cost_for_small_item NUMERIC(4, 2) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE kpy_boske_fulfillment_cost ADD CONSTRAINT FK_75965D585080ECDE FOREIGN KEY (warehouse_id) REFERENCES kpy_warehouse (id)');
        $this->addSql('ALTER TABLE kpy_product ADD CONSTRAINT FK_701B6A3F5080ECDE FOREIGN KEY (warehouse_id) REFERENCES kpy_warehouse (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kpy_boske_fulfillment_cost DROP FOREIGN KEY FK_75965D585080ECDE');
        $this->addSql('ALTER TABLE kpy_product DROP FOREIGN KEY FK_701B6A3F5080ECDE');
        $this->addSql('DROP TABLE kpy_boske_fulfillment_cost');
        $this->addSql('DROP TABLE kpy_product');
        $this->addSql('DROP TABLE kpy_product_prices');
        $this->addSql('DROP TABLE kpy_warehouse');
    }
}
