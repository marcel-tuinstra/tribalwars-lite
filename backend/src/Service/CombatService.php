<?php

namespace App\Service;

use App\Entity\Village;
use Psr\Log\LoggerInterface;

class CombatService
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function resolveCombat(Village $attacker, Village $defender): void
    {
        $attackerPower = $this->calculateAttackPower($attacker);
        $defenderPower = $this->calculateDefensePower($defender);

        $this->logger->info(sprintf('Attacker Power: %d | Defender Power: %d', $attackerPower, $defenderPower));

        $baseLossFactor = 0.2;

        $attackerLossRatio = $defenderPower > 0
            ? min(1.0, ($defenderPower / $attackerPower) * $baseLossFactor)
            : 0;

        $defenderLossRatio = $attackerPower > 0
            ? min(1.0, ($attackerPower / $defenderPower) * $baseLossFactor)
            : 0;

        $this->logger->info(sprintf('Attacker loses %.0f%% | Defender loses %.0f%%', $attackerLossRatio * 100, $defenderLossRatio * 100));

        $this->applyTroopLosses($attacker, $attackerLossRatio);
        $this->applyTroopLosses($defender, $defenderLossRatio);

        if ($attackerPower > $defenderPower) {
            $this->plunderResources($defender);
            $this->logger->info('Attack successful. Resources plundered.');
        } else {
            $this->logger->info('Attack repelled. No resources lost.');
        }
    }

    private function calculateAttackPower(Village $village): int
    {
        $power = 0;
        foreach ($village->getTroops() as $troop) {
            $power += $troop->getAmount() * $troop->getPower()->getAttack();
        }
        return $power;
    }

    private function calculateDefensePower(Village $village): int
    {
        $power = 0;
        foreach ($village->getTroops() as $troop) {
            $power += $troop->getAmount() * $troop->getPower()->getDefense();
        }
        return $power;
    }

    private function applyTroopLosses(Village $village, float $lossRatio): void
    {
        foreach ($village->getTroops() as $troop) {
            $losses = (int)round($troop->getAmount() * $lossRatio);
            $troop->decreaseAmount($losses);
        }
    }

    private function plunderResources(Village $defender): void
    {
        foreach ($defender->getResources() as $resource) {
            $stolen = (int)($resource->getAmount() * 0.1);
            $resource->decreaseAmount($stolen);
        }
    }
}