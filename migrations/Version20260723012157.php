<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260723012157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Igy jar az aki reflexbol postgreSQL-el indul. ¯\_(ツ)_/¯';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->addSql('DELETE FROM review older USING review newer WHERE older.company_id = newer.company_id AND older.author_email = newer.author_email AND older.id > newer.id');
        } else {
            $this->addSql('DELETE older FROM review older INNER JOIN review newer ON older.company_id = newer.company_id AND older.author_email = newer.author_email AND older.id > newer.id');
        }

        $this->addSql('CREATE UNIQUE INDEX review_company_author_uniq ON review (company_id, author_email)');
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->addSql('DROP INDEX review_company_author_uniq');
        } else {
            $this->addSql('DROP INDEX review_company_author_uniq ON review');
        }
    }
}
