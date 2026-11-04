<?php

namespace App\Identity\Application;

use App\Identity\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminCommand
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[AsCommand('kpy:identity:create-root')]
    public function __invoke(
        InputInterface                              $input,
        OutputInterface                             $output,
        UserPasswordHasherInterface                 $passwordHasher,
        #[Autowire('%env(ADMIN_USERNAME)%')] string $defaultUsername,
        #[Autowire('%env(ADMIN_PASSWORD)%')] string $defaultPassword
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($this->em->getRepository(User::class)->findOneBy(['username' => $defaultUsername])) {
                $io->error('The superadmin user already exists!!');
                return Command::FAILURE;
            }

            $superadmin = new User();
            $superadmin
                ->setUsername($defaultUsername)
                ->setEmail('root@kompymascotas.com')
                ->setFirstname('root')
                ->setLastname('admin')
                ->grantRootAccess();

            $superadmin->setPassword($passwordHasher->hashPassword($superadmin, $defaultPassword));

            $this->em->persist($superadmin);
            $this->em->flush();

            $io->success('The superadmin user has been created correctly');

            return Command::SUCCESS;

        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
