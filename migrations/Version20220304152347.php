<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220304152347 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE `user_watched_movie` (
                `user_id` INT NOT NULL,
                `movie_id` INT NOT NULL,
                PRIMARY KEY (`user_id`, `movie_id`),
                FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
                FOREIGN KEY (`movie_id`) REFERENCES `movie` (`tmdb_id`)
            )
        ');

        $this->addSql('ALTER TABLE movie RENAME COLUMN tmdb_id TO id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `user_watched_movie`');
        $this->addSql('ALTER TABLE movie RENAME COLUMN id TO tmdb_id');
    }
}

