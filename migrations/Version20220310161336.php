<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220310161336 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        foreach([
                    'DROP FOREIGN KEY `request_user_id`',
                    'ADD CONSTRAINT `request_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)',
                ] as $query) {
            $this->addSql("ALTER TABLE `request` ");
        }
    }

    public function down(Schema $schema): void
    {
        foreach([
                    'DROP FOREIGN KEY `request_user_id`',
                    'ADD CONSTRAINT `request_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE ON DELETE CASCADE',
                ] as $query) {
            $this->addSql("ALTER TABLE `request` ");
        }
    }
}
