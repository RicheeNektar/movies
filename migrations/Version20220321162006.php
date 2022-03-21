<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Doctrine\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20220321162006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->createBackup(
            $this->connection->createQueryBuilder()
                ->select('*')
                ->from('request')
                ->fetchAllAssociative()
        );

        $this->addSql('DROP TABLE request');

        $this->addSql('
            CREATE TABLE request (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                movie_id INT NOT NULL,
                CONSTRAINT fk_requests_user_id FOREIGN KEY (user_id) REFERENCES user (id),
                CONSTRAINT fk_requests_movie_id FOREIGN KEY (movie_id) REFERENCES movie (id)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->createBackup(
            $this->connection->createQueryBuilder()
                ->select('*')
                ->from('request')
                ->fetchAllAssociative()
        );

        $this->addSql('DROP TABLE request');

        $this->addSql('
            CREATE TABLE request (
                user_id INT NOT NULL,
                tmdb_id INT NOT NULL,
                CONSTRAINT fk_requests_user_id FOREIGN KEY (user_id) REFERENCES user (id),
                CONSTRAINT fk_requests_tmdb_id FOREIGN KEY (tmdb_id) REFERENCES movie (id)
            )
        ');
    }
}
