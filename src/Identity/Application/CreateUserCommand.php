<?php

namespace App\Identity\Application;

use App\Identity\Domain\ValueObject\CreateUserInput;
use App\Identity\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\MapInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class CreateUserCommand
{

    #[AsCommand('kpy:identity:create-user')]
    public function __invoke(
        InputInterface              $input,
        OutputInterface             $output,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        #[MapInput] CreateUserInput $userInput
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($em->getRepository(User::class)->findOneBy(['username' => strtolower($userInput->username)])) {
                $io->error('Ya existe un usuario con ese nombre de usuario');
                return Command::FAILURE;
            }

            if ($em->getRepository(User::class)->findOneBy(['email' => strtolower($userInput->email)])) {
                $io->error('Ya existe un usuario con ese nombre de usuario');
                return Command::FAILURE;
            }

            $user = new User();
            $user
                ->setUsername(strtolower($userInput->username))
                ->setEmail(strtolower($userInput->email))
                ->setFirstname($userInput->firstName)
                ->setLastname($userInput->lastName);

            $user->setPassword($passwordHasher->hashPassword($user, $userInput->password));

            $em->persist($user);
            $em->flush();

            $io->success('The user has been created');
            return Command::SUCCESS;

        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
