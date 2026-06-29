<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629162000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE connectif_product (id_product INT NOT NULL, id_product_attribute INT NOT NULL, sync_at DATETIME DEFAULT NULL, extra_tags JSON DEFAULT NULL, PRIMARY KEY (id_product, id_product_attribute)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE connectif_product_related (id_product_related INT NOT NULL, id_product_attribute_related INT NOT NULL, id_product INT NOT NULL, PRIMARY KEY (id_product_related, id_product_attribute_related, id_product)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE connectif_product');
        $this->addSql('DROP TABLE connectif_product_related');
    }
}
