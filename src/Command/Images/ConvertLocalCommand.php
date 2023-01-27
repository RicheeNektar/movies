<?php

namespace App\Command\Images;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function PHPUnit\Framework\directoryExists;

class ConvertLocalCommand extends Command
{
    protected static $defaultName = 'app:images:convert-local';
    protected static $defaultDescription = 'Converts local images in the images folder to webp';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $files = scandir('images');
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            if (preg_match('/\w+\.png$/', $file)) {
                $path =  "public/images/$filename.webp";

                $dir = dirname($path);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                $image = imagecreatefrompng("images/$file");
                imagepalettetotruecolor($image);
                imagesavealpha($image, true);
                imageantialias($image, true);
                imagewebp($image, $path);
                imagedestroy($image);
                $io->writeln("Converted $filename");
            } else {
                $io->writeln("Skipping $filename");
            }
        }

        return Command::SUCCESS;
    }
}
