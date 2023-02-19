<?php

namespace App\Command\Images;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function PHPUnit\Framework\directoryExists;

#[AsCommand('app:images:convert-local', 'Converts local images in the images folder to webp')]
final class ConvertLocalCommand extends Command
{
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
