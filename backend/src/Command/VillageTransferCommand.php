<?php

namespace App\Command;

use App\Entity\Player;
use App\Entity\Village;
use App\Repository\PlayerRepository;
use App\Repository\VillageRepository;
use App\Service\PlayerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\VarDumper;

#[AsCommand(
    name: 'app:village:transfer',
    description: 'Transfer village to another player.',
)]
class VillageTransferCommand extends Command
{
    const INPUT_VILLAGE = 'village';
    const INPUT_PLAYER  = 'player';

    public function __construct(
        private readonly PlayerService     $playerService,
        private readonly PlayerRepository  $playerRepository,
        private readonly VillageRepository $villageRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::INPUT_PLAYER, InputArgument::REQUIRED, 'The player to transfer to, in the format: player_email')
            ->addArgument(self::INPUT_VILLAGE, InputArgument::REQUIRED, 'The village to transfer, in the format: x,y');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io                 = new SymfonyStyle($input, $output);
        $playerEmail        = $input->getArgument(self::INPUT_PLAYER);
        $villageCoordinates = explode(',', $input->getArgument(self::INPUT_VILLAGE));

        $player        = $this->playerRepository->findOneByEmail($playerEmail);
        $village       = $this->villageRepository->findOneByCoordinates($villageCoordinates[0], $villageCoordinates[1]);
        $originalOwner = $village->getPlayer();
        $this->playerService->transferVillageOwnership($player, $village);

        $io->success(sprintf('Transferred village [%d,%d] from [%s] to %s', $village->getX(), $village->getY(), $originalOwner?->getEmail(), $player->getEmail()));

        return Command::SUCCESS;
    }
}
