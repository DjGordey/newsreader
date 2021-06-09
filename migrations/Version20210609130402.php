<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210609130402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE news_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE source_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE news (id INT NOT NULL, source_id INT NOT NULL, title VARCHAR(255) NOT NULL, preview VARCHAR(255) DEFAULT NULL, text TEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, date_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1DD39950953C1C61 ON news (source_id)');
        $this->addSql('CREATE TABLE source (id INT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, selector VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950953C1C61 FOREIGN KEY (source_id) REFERENCES source (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE news DROP CONSTRAINT FK_1DD39950953C1C61');
        $this->addSql('DROP SEQUENCE news_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE source_id_seq CASCADE');
        $this->addSql('DROP TABLE news');
        $this->addSql('DROP TABLE source');
    }
}
