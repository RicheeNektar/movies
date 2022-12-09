<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220813092809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE backdrop (id INT AUTO_INCREMENT NOT NULL, movie_id INT NOT NULL, file VARCHAR(32) NOT NULL, INDEX IDX_EBFEDE688F93B6FC (movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE episode (id INT AUTO_INCREMENT NOT NULL, season_id INT DEFAULT NULL, series_id INT DEFAULT NULL, episode_id INT NOT NULL, title VARCHAR(255) NOT NULL, air_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_DDAA1CDA4EC001D1 (season_id), INDEX IDX_DDAA1CDA5278319C (series_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(64) NOT NULL, text VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B6BD307FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movie (id INT NOT NULL, title VARCHAR(255) NOT NULL, poster VARCHAR(32) NOT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_hidden TINYINT(1) NOT NULL, air_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movie_backdrop (id INT AUTO_INCREMENT NOT NULL, movie_id INT DEFAULT NULL, file VARCHAR(32) NOT NULL, INDEX IDX_370845E58F93B6FC (movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movies (tmdb_id INT NOT NULL, title VARCHAR(255) NOT NULL, file VARCHAR(255) NOT NULL, poster VARCHAR(255) NOT NULL, PRIMARY KEY(tmdb_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request (id INT AUTO_INCREMENT NOT NULL, movie_id INT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_3B978F9F8F93B6FC (movie_id), INDEX IDX_3B978F9FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE season (id INT AUTO_INCREMENT NOT NULL, series_id INT DEFAULT NULL, season_id INT NOT NULL, poster VARCHAR(32) NOT NULL, name VARCHAR(255) NOT NULL, air_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_F0E45BA95278319C (series_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE series (id INT NOT NULL, title VARCHAR(255) NOT NULL, poster VARCHAR(32) NOT NULL, last_updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', air_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE series_backdrop (id INT AUTO_INCREMENT NOT NULL, series_id INT DEFAULT NULL, file VARCHAR(32) NOT NULL, INDEX IDX_B81546675278319C (series_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, access_token VARCHAR(88) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649B6A2DD68 (access_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_movie (user_id INT NOT NULL, movie_id INT NOT NULL, INDEX IDX_FF9C0937A76ED395 (user_id), INDEX IDX_FF9C09378F93B6FC (movie_id), PRIMARY KEY(user_id, movie_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_episode (user_id INT NOT NULL, episode_id INT NOT NULL, INDEX IDX_85A702D0A76ED395 (user_id), INDEX IDX_85A702D0362B62A0 (episode_id), PRIMARY KEY(user_id, episode_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE backdrop ADD CONSTRAINT FK_EBFEDE688F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA5278319C FOREIGN KEY (series_id) REFERENCES series (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE movie_backdrop ADD CONSTRAINT FK_370845E58F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F8F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA95278319C FOREIGN KEY (series_id) REFERENCES series (id)');
        $this->addSql('ALTER TABLE series_backdrop ADD CONSTRAINT FK_B81546675278319C FOREIGN KEY (series_id) REFERENCES series (id)');
        $this->addSql('ALTER TABLE user_movie ADD CONSTRAINT FK_FF9C0937A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_movie ADD CONSTRAINT FK_FF9C09378F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_episode ADD CONSTRAINT FK_85A702D0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_episode ADD CONSTRAINT FK_85A702D0362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_episode DROP FOREIGN KEY FK_85A702D0362B62A0');
        $this->addSql('ALTER TABLE backdrop DROP FOREIGN KEY FK_EBFEDE688F93B6FC');
        $this->addSql('ALTER TABLE movie_backdrop DROP FOREIGN KEY FK_370845E58F93B6FC');
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F8F93B6FC');
        $this->addSql('ALTER TABLE user_movie DROP FOREIGN KEY FK_FF9C09378F93B6FC');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA4EC001D1');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA5278319C');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA95278319C');
        $this->addSql('ALTER TABLE series_backdrop DROP FOREIGN KEY FK_B81546675278319C');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA76ED395');
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9FA76ED395');
        $this->addSql('ALTER TABLE user_movie DROP FOREIGN KEY FK_FF9C0937A76ED395');
        $this->addSql('ALTER TABLE user_episode DROP FOREIGN KEY FK_85A702D0A76ED395');
        $this->addSql('DROP TABLE backdrop');
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_backdrop');
        $this->addSql('DROP TABLE movies');
        $this->addSql('DROP TABLE request');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE series');
        $this->addSql('DROP TABLE series_backdrop');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_movie');
        $this->addSql('DROP TABLE user_episode');
    }
}
