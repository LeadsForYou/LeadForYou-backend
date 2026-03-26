<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adiciona coluna deleted_at para soft delete nas tabelas leads, users e stages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE leads ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE stages ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE leads DROP deleted_at');
        $this->addSql('ALTER TABLE users DROP deleted_at');
        $this->addSql('ALTER TABLE stages DROP deleted_at');
    }
}
