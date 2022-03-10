<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220310180033 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE series RENAME COLUMN tmdb_id TO id');

        $this->addSql('
            ALTER TABLE series
                ADD last_updated DATETIME,
                ADD air_date DATE
        ');
        $this->addSql('
            ALTER TABLE season
                ADD air_date DATE
        ');
        $this->addSql('
            ALTER TABLE episode
                ADD air_date DATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE series RENAME COLUMN id TO tmdb_id');

        $this->addSql('ALTER TABLE series DROP air_date, DROP last_updated');
        $this->addSql('ALTER TABLE season DROP air_date');
        $this->addSql('ALTER TABLE episode DROP air_date');
    }
}
