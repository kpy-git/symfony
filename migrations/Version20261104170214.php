<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261104170214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, firstname VARCHAR(50) NOT NULL, lastname VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(50) NOT NULL, roles JSON NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE kpy_product CHANGE weight weight DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE warehouse_shipment CHANGE parcels parcels INT NOT NULL, CHANGE weight weight NUMERIC(10, 6) NOT NULL, CHANGE warehouse_id warehouse_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE kpy_product CHANGE weight weight DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE warehouse_shipment CHANGE parcels parcels INT DEFAULT 1 NOT NULL, CHANGE weight weight NUMERIC(10, 6) DEFAULT \'1.000000\' NOT NULL, CHANGE warehouse_id warehouse_id INT DEFAULT 1');
    }
}
