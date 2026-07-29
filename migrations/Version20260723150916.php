<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260723150916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kpy_product ADD weight DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('CREATE TABLE priceshape_brand_included (id_manufacturer INT NOT NULL, created_at DATETIME NOT NULL, fixed_price TINYINT NOT NULL, PRIMARY KEY (id_manufacturer)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kpy_product DROP weight');
        $this->addSql('DROP TABLE priceshape_brand_included');
    }
}
