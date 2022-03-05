<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211228113938 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE user (
                id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                username VARCHAR(180) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                roles JSON NOT NULL
            ) DEFAULT CHARACTER SET UTF8
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user');
    }
}
