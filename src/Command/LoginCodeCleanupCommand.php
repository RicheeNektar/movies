<?php

namespace App\Command;

use App\Entity\UserMail;
use App\Repository\LoginCodeRepository;
use App\Repository\UserMailRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:cleanup:login-codes', 'Cleans up all login-codes older than 15 Minutes')]
final class LoginCodeCleanupCommand extends Command
{
    private LoginCodeRepository $loginCodeRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LoginCodeRepository $loginCodeRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->loginCodeRepository = $loginCodeRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $expiredCodes = $this->loginCodeRepository
            ->createQueryBuilder('lc')
            ->andWhere('lc.createdAt > :expire')
            ->setParameter('expire', new DateTimeImmutable('-15 Minutes'))
            ->andWhere('lc.usedAt IS NULL')
            ->getQuery()
            ->getResult();

        foreach ($expiredCodes as $expiredCode) {
            $this->entityManager->remove($expiredCode);
        }
        $this->entityManager->flush();

        $io->info('Deleted ' . count($expiredCodes) . ' codes');
        return Command::SUCCESS;
    }
}
