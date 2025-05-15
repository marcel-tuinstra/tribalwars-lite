<?php

namespace App\EventListener;

use App\Event\TroopTrainedEvent;
use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Psr\Log\LoggerInterface;

final class TroopTrainedListener
{
    public function __construct(
        private readonly LoggerInterface     $logger,
        private readonly NotificationService $notificationService
    )
    {
    }

    #[AsEventListener]
    public function onTroopTrainedEvent(TroopTrainedEvent $event): void
    {
        $troop   = $event->getTroop();
        $village = $troop->getVillage();
        $player  = $village->getPlayer();

        $this->logger->info(sprintf(
            'Trained %d units of %s (Village ID: %d)',
            $troop->getTrainingQueue(),
            $troop->getRole()->value(),
            $village->getId()
        ));

        if ($player) {
            $this->notificationService->createNotification($player, 'troop_trained', [
                'village' => $village->getName(),
                'troop'   => $troop->getRole()->value(),
                'amount'  => $troop->getTrainingQueue()
            ]);
        }
    }
}