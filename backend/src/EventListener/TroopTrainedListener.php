<?php

namespace App\EventListener;

use App\Event\TroopTrainedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Psr\Log\LoggerInterface;

final class TroopTrainedListener
{
    public function __construct(private readonly LoggerInterface $logger) {}

    #[AsEventListener]
    public function onTroopTrainedEvent(TroopTrainedEvent $event): void
    {
        $troop = $event->getTroop();
        $this->logger->info(sprintf(
            'Trained %d units of %s (Village ID: %d)',
            $troop->getTrainingQueue(),
            $troop->getRole()->value(),
            $troop->getVillage()->getId()
        ));
    }
}