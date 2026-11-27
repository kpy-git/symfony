<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261127092513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE priceshape_product_pvpr CHANGE country country VARCHAR(2) DEFAULT \'es\' NOT NULL, CHANGE pvpr pvpr NUMERIC(6, 2) DEFAULT 0 NOT NULL, CHANGE min_pvp min_pvp NUMERIC(6, 2) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE priceshape_product_pvpr CHANGE country country VARCHAR(2) NOT NULL, CHANGE pvpr pvpr NUMERIC(6, 2) NOT NULL, CHANGE min_pvp min_pvp NUMERIC(6, 2) NOT NULL');
    }
}
