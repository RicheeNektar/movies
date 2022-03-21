<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220318145108 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE request
                ADD CONSTRAINt fk_r_user_id FOREIGN KEY (user_id) REFERENCES user (id),
                ADD CONSTRAINt fk_r_tmdb_id FOREIGN KEY (tmdb_id) REFERENCES movie (id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE request
                DROP FOREIGN KEY fk_r_user_id,
                DROP FOREIGN KEY fk_r_tmdb_id
        ');
    }
}
