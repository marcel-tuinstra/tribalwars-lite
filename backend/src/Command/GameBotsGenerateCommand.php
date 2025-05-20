<?php

namespace App\Command;

use App\Repository\VillageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Service\BotService;
use App\Service\WorldMapService;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:game:bots-generate',
    description: 'Generates a new wave of bot villages starting from the center outward.'
)]
class GameBotsGenerateCommand extends Command
{
    public function __construct(
        private readonly BotService             $botService,
        private readonly VillageRepository      $villageRepository,
        private readonly EntityManagerInterface $em
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('amount', null, InputOption::VALUE_OPTIONAL, 'How many bot villages to generate', 25);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io      = new SymfonyStyle($input, $output);
        $amount  = (int)$input->getOption('amount');
        $created = 0;
        $taken   = [];

        while ($created < $amount) {
            $village = $this->botService->createVillage(taken: $taken);

            if (!$village) {
                $io->warning("Stopped after $created villages: no available space.");
                break;
            }

            $created++;
        }

        $this->em->flush();

        $io->success("Successfully created $created bot village(s).");

        return Command::SUCCESS;
    }
}
