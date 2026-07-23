<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722212124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX company_name_uniq (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE review ADD company_id INT NOT NULL, DROP company_name');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_794381C6979B1AD6 ON review (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE company');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6979B1AD6');
        $this->addSql('DROP INDEX IDX_794381C6979B1AD6 ON review');
        $this->addSql('ALTER TABLE review ADD company_name VARCHAR(255) NOT NULL, DROP company_id');
    }
}
