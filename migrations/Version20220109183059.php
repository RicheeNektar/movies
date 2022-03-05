<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220109183059 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE request DROP poster
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE request ADD poster VARCHAR(32) NOT NULL
        ');
    }
}
