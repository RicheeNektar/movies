<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220223205249 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $movies = $this->connection->fetchAllAssociative('
            SELECT tmdb_id FROM movie
        ');

        $this->addSql('
            ALTER TABLE movie ADD creation_date DATE
        ');

        foreach ($movies as $movie) {
            $tmdbId = $movie['tmdb_id'];
            $date = new \DateTimeImmutable(date('Y-m-d', filemtime("movies/$tmdbId.mp4")));

            $this->addSql($this->connection
                ->createQueryBuilder()
                ->update('movie')
                ->set('creation_date', $date->format('"Y-m-d H:m:s"'))
                ->where("tmdb_id = $tmdbId")
                ->getSQL()
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE movie DROP creation_date
        ');
    }
}
