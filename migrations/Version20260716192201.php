<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716192201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kpy_product ADD weight DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE is_jirafa is_jirafa TINYINT DEFAULT 0 NOT NULL, CHANGE is_pack is_pack TINYINT DEFAULT 0 NOT NULL, CHANGE weight weight DOUBLE PRECISION DEFAULT 0 NOT NULL, ADD brand_id SMALLINT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kpy_product CHANGE is_jirafa is_jirafa TINYINT NOT NULL, CHANGE is_pack is_pack TINYINT NOT NULL, CHANGE weight weight DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE kpy_product DROP weight');
        $this->addSql('ALTER TABLE kpy_product DROP brand_id');
    }
}
