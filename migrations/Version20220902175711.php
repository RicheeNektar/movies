<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220902175711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_mail (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', verified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', verification_code INT NOT NULL, mail VARCHAR(320) NOT NULL, INDEX IDX_2BA7E081A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_mail ADD CONSTRAINT FK_2BA7E081A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP() COMMENT \'(DC2Type:datetime_immutable)\'');
        // $this->addSql('UPDATE user SET roles = CONCAT(SUBSTR(roles, -1), \', "ROLE_VERIFIED"]\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_mail');
        $this->addSql('ALTER TABLE backdrop CHANGE file file VARCHAR(32) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE episode CHANGE title title VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE message CHANGE title title VARCHAR(64) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE text text VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE movie CHANGE title title VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE poster poster VARCHAR(32) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE description description VARCHAR(4096) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE movie_backdrop CHANGE file file VARCHAR(32) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE movies CHANGE title title VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE file file VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE poster poster VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE season CHANGE poster poster VARCHAR(32) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE series CHANGE title title VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE poster poster VARCHAR(32) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE description description VARCHAR(4096) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE series_backdrop CHANGE file file VARCHAR(32) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE user DROP created_at, CHANGE username username VARCHAR(180) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE password password VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE access_token access_token VARCHAR(88) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`');
    }
}
