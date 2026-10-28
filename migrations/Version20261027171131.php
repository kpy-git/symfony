<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261027171131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE warehouse_shipment ADD parcels INT NOT NULL DEFAULT 1, ADD weight NUMERIC(10, 6) NOT NULL DEFAULT 1.0, ADD warehouse_id INT DEFAULT 1');
        $this->addSql('ALTER TABLE warehouse_shipment ADD CONSTRAINT FK_2283427A5080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('CREATE INDEX IDX_2283427A5080ECDE ON warehouse_shipment (warehouse_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE warehouse_shipment DROP FOREIGN KEY FK_2283427A5080ECDE');
        $this->addSql('DROP INDEX IDX_2283427A5080ECDE ON warehouse_shipment');
        $this->addSql('ALTER TABLE warehouse_shipment DROP parcels, DROP weight, DROP warehouse_id');
    }
}
