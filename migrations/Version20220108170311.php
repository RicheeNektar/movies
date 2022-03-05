<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220108170311 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE request (
                tmdb_id INT NOT NULL PRIMARY KEY,
                poster VARCHAR(32) NOT NULL
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE request');
    }
}
