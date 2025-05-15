<?php

namespace App\EventListener;

use App\Event\AttackLaunchedEvent;
use App\Service\CombatService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class AttackLaunchedListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CombatService   $combatService
    )
    {
    }

    #[AsEventListener]
    public function onAttackLaunched(AttackLaunchedEvent $event): void
    {
        $attacker = $event->getAttacker();
        $defender = $event->getDefender();

        $this->logger->info(sprintf(
            'Attack launched from Village ID %d to Village ID %d',
            $attacker->getId(),
            $defender->getId()
        ));

        $this->combatService->resolveCombat($attacker, $defender);
    }
}