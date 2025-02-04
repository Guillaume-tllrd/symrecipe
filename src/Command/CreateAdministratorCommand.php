<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-administrator',
    description: 'Create an administrator',
)]
class CreateAdministratorCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        // ne pas oublier de mettre le construct du parent qui demande le nom de la commande en paramètre
        parent::__construct('app:create-administrator');
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('full_name', InputArgument::OPTIONAL, 'full Name')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);

        $fullName = $input->getArgument('full_name');
        // si on a pas de fullName on peut faire appeler une commande de symfony en posant une question avec new Question
        if (!$fullName) {
            $question = new Question('Quel est le nom de l\'administrateur ? ');
            // on fait appelle à getHelper
            $fullName = $helper->ask($input, $output, $question);
        }
        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Quel est l\'email de ' . $fullName . ' ? ');
            // on fait appelle à getHelper
            $email = $helper->ask($input, $output, $question);
        }
        $plainPassword = $input->getArgument('password');
        if (!$plainPassword) {
            $question = new Question('Quel est le mot de passe de ' . $fullName . ' ? ');
            // on fait appelle à getHelper
            $plainPassword = $helper->ask($input, $output, $question);
        }

        $user = (new User())->setFullName($fullName)->setEmail($email)->setPlainPassword($plainPassword)->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        // Pour le persist on utilise entityManager que l'on add dans le construct
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $io->success('Le nouvel administrateur a été créé .');

        return Command::SUCCESS;
    }
}
