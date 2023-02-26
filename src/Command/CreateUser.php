<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\UserMail;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('app:create-user')]
final class CreateUser extends Command
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    protected function configure()
    {
        $this->addOption('make-admin');
        $this->addArgument('username', InputArgument::REQUIRED);
        $this->addArgument('password', InputArgument::REQUIRED);
        $this->addArgument('mail', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        $isAdmin = $input->getOption('make-admin');
        $password = $input->getArgument('password');

        $exists = $this->userRepository->count([
            'username' => $username,
        ]) > 0;

        if ($exists) {
            $io->error("User '$username' already exists.");
            return Command::FAILURE;
        }

        $user = new User();
        $user->setUsername($username);

        if ($isAdmin) {
            $user->addRole('ROLE_ADMIN');
        }

        $userMail = new UserMail();
        $userMail->setMail($input->getArgument('mail'));
        $userMail->setVerifiedAt(new \DateTimeImmutable());
        $userMail->setVerificationCode(random_int(111111,999999));
        $user->addRole('ROLE_VERIFIED');

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
        $user->addUserMail($userMail);

        $this->entityManager->persist($userMail);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User added');
        return Command::SUCCESS;
    }
}
