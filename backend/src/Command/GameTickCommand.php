<?php

namespace App\Command;

use App\Repository\VillageRepository;
use App\Service\TickProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:game:tick',
    description: 'Processes all villages: resource generation, training, upgrades, and bot turns.',
)]
/**
 * Runs the main game tick.
 *
 * Cron example (every minute):
 * * * * * cd /path/to/project && php bin/console app:game:tick > /dev/null 2>&1
 */
class GameTickCommand extends Command
{
    public function __construct(
        private readonly VillageRepository $villageRepository,
        private readonly TickProcessor     $tickProcessor,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $villages = $this->villageRepository->findAll();

        foreach ($villages as $village) {
            $this->tickProcessor->processVillage($village);
            $io->info(sprintf('Tick processed for village [%s]', $village->getId()));
        }

        $io->success(sprintf('Tick processed for [%d] villages.', count($villages)));

        return Command::SUCCESS;
    }
}
