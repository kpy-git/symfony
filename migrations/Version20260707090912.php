<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707090912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE priceshape_brand_banned (id_manufacturer INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id_manufacturer)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE priceshape_product_pvpr (id_product INT UNSIGNED NOT NULL, id_product_attribute INT UNSIGNED NOT NULL, country VARCHAR(2) NOT NULL, pvpr NUMERIC(6, 2) NOT NULL, PRIMARY KEY (id_product, id_product_attribute)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE priceshape_brand_fixed_price (id_manufacturer INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id_manufacturer)) DEFAULT CHARACTER SET utf8mb4)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE priceshape_brand_banned');
        $this->addSql('DROP TABLE priceshape_product_pvpr');
        $this->addSql('DROP TABLE priceshape_brand_fixed_price');
    }
}
