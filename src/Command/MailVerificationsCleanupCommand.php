<?php

namespace App\Command;

use App\Entity\UserMail;
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

#[AsCommand('app:cleanup:mail-verifications', 'Cleans up all verifications older than 24 Hours and user accounts if it has no addresses')]
final class MailVerificationsCleanupCommand extends Command
{
    private UserMailRepository $userMailRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserMailRepository $userMailRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->userMailRepository = $userMailRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $deletedMails = 0;

        // Delete unverified mails older than a day
        $userMails = $this->userMailRepository->createQueryBuilder('um')
            ->andWhere('um.verifiedAt IS NULL')
            ->andWhere('um.createdAt < :expire')
            ->setParameter('expire', new DateTimeImmutable("-1 Day"))
            ->getQuery()
            ->getResult();

        foreach ($userMails as $userMail) {
            $this->entityManager->remove($userMail);
            $io->info("Deleted UserMail " . $userMail->getId());
            $deletedMails++;
        }

        $this->entityManager->flush();

        $io->info("Deleted $deletedMails mails");
        return Command::SUCCESS;
    }
}
