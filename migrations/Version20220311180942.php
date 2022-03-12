<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220311180942 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE movie MODIFY creation_date DATETIME');
        $this->addSql('ALTER TABLE movie DROP year');
        $this->addSql('ALTER TABLE movie ADD air_date DATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE movie MODIFY creation_date DATE');
        $this->addSql('ALTER TABLE movie DROP air_date');
        $this->addSql('ALTER TABLE movie ADD year INT(4) NOT NULL');
    }
}
