<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220811170521 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX i_episode ON episode (id)');
        $this->addSql("
            CREATE TABLE `user_watched_episode` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `series_id` INT NOT NULL,
                `season_id` INT NOT NULL,
                `episode_id` INT NOT NULL,
                `user_id` INT NOT NULL
            )
        ");
        $this->addSql("SELECT concat('ALTER TABLE ', TABLE_NAME, ' DROP FOREIGN KEY ', CONSTRAINT_NAME, ';') 
FROM information_schema.key_column_usage 
WHERE CONSTRAINT_SCHEMA = 'db_name' 
AND referenced_table_name IS NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE `user_watched_episode`");
    }
}
