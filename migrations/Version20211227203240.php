<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211227203240 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE backdrop RENAME movie_backdrop");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS series (
                tmdb_id INT NOT NULL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                poster VARCHAR(32) NOT NULL
            ) DEFAULT CHARACTER SET UTF8
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS season (
                id INT NOT NULL,
                series_id INT NOT NULL,
                PRIMARY KEY (series_id, id),
                INDEX i_se_id (id),
                FOREIGN KEY (series_id) REFERENCES series (tmdb_id)  ON DELETE CASCADE ON UPDATE CASCADE
            ) DEFAULT CHARACTER SET UTF8
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS episode (
                id INT NOT NULL,
                season_id INT NOT NULL,
                series_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                PRIMARY KEY (season_id, series_id, id),
                FOREIGN KEY (season_id) REFERENCES season (id)  ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (series_id) REFERENCES series (tmdb_id) ON DELETE CASCADE ON UPDATE CASCADE
            ) DEFAULT CHARACTER SET UTF8
        ");

        $this->addSql('
            CREATE TABLE IF NOT EXISTS series_backdrop (
                id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                file VARCHAR(32) NOT NULL,
                series_id INT NOT NULL,
                FOREIGN KEY (series_id) REFERENCES series (tmdb_id) ON DELETE CASCADE ON UPDATE CASCADE
            ) DEFAULT CHARACTER SET utf8
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE movie_backdrop RENAME backdrop");
        $this->addSql('DROP TABLE series_backdrop');
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE series');
    }
}
