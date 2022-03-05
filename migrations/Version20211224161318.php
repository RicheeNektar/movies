<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211224161318 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE movie (
                tmdb_id INT NOT NULL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                poster VARCHAR(32) NOT NULL
            ) CHARACTER SET utf8
        ');

        $this->addSql('
            CREATE TABLE backdrop (
                id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                file VARCHAR(32) NOT NULL,
                movie_id INT NOT NULL,
                INDEX IDX_EBFEDE689B6BBAF4 (movie_id),
                FOREIGN KEY (movie_id) REFERENCES movie (tmdb_id) ON DELETE CASCADE ON UPDATE CASCADE
            ) CHARACTER SET utf8
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE backdrop');
        $this->addSql('DROP TABLE movie');
    }
}
