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

class MailVerificationsCleanupCommand extends Command
{
    protected static $defaultName = 'app:mail-verifications:cleanup';
    protected static $defaultDescription = 'Cleans up all verifications older than 24 Hours and user accounts if it has no addresses';

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
        $deletedUsers = 0;

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

            $user = $userMail->getUser();

            if (count($user->getUserMails()) === 1) {
                $this->entityManager->remove($user);
                $io->info("Deleted User " . $user->getUserIdentifier() . " with no more mails");
                $deletedUsers++;
            }
        }

        $usersWithoutMail = $this->userRepository->createQueryBuilder('u')
            ->leftJoin(UserMail::class, 'um', Join::WITH, 'u.id = um.user')
            ->where('um.mail IS NULL')
            ->getQuery()
            ->getResult();

        foreach ($usersWithoutMail as $userWithoutMail) {
            $this->entityManager->remove($userWithoutMail);
            $io->info("Deleted user " . $userWithoutMail->getId() . " with no mails");
            $deletedUsers++;
        }

        $this->entityManager->flush();

        $io->info("Deleted $deletedUsers users and $deletedMails mails");
        return Command::SUCCESS;
    }
}
