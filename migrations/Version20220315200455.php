<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220315200455 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE message (
                id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                user_id INT NOT NULL,
                title VARCHAR(64) NOT NULL,
                text VARCHAR(255) NOT NULL,
                create_at DATETIME NOT NULL,
                CONSTRAINT fk_m_user_id FOREIGN KEY (user_id) REFERENCES user (id)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE message');
    }
}
