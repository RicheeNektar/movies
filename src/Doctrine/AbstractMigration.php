<?php

namespace App\Doctrine;

use Doctrine\DBAL\Schema\Schema;

abstract class AbstractMigration extends \Doctrine\Migrations\AbstractMigration
{
    protected function createBackup(array $data): void
    {
        if (count($data) > 0) {
            preg_match('/Version\d+/', get_class($this), $matches);
            $fileName = join('-', [
                    $matches[0],
                    debug_backtrace()[1]['function'],
                    'backup',
                    (new \DateTimeImmutable())->format('YmdHis')
                ]) . '.csv';

            $keys = array_keys($data[0]);
            file_put_contents($fileName, join(',', $keys) . "\n");

            foreach ($data as $row) {
                file_put_contents($fileName, join(',', $row) . "\n", FILE_APPEND);
            }
        }
    }

    public abstract function up(Schema $schema): void;
}