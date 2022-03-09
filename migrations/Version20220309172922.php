<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use phpDocumentor\Reflection\Types\Integer;

final class Version20220309172922 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        foreach([
            'ADD `user_id` INT',
            'DROP PRIMARY KEY, ADD PRIMARY KEY (`tmdb_id`, `user_id`)',
            'ADD CONSTRAINT `request_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE ON DELETE CASCADE',
        ] as $query) {
            $this->addSql("ALTER TABLE `request` ");
        }
    }

    public function down(Schema $schema): void
    {
        foreach([
            'DROP FOREIGN KEY `request_user_id`',
            'DROP PRIMARY KEY, ADD PRIMARY KEY (`tmdb_id`)',
            'DROP `user_id`',
        ] as $query) {
            $this->addSql("ALTER TABLE `request` $query");
        }
    }
}
