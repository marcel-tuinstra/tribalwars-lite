<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\Village;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class PlayerService
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly WorldMapService $worldMapService)
    {
    }

    public function createVillage(Player $player): Village
    {
        $coords = $this->worldMapService->findAvailableCoordinateWithinExpandingRadius();

        if (!$coords) {
            throw new RuntimeException('No available space for player village.');
        }

        $village = new Village(sprintf("%s's Village", $player->getName()), $coords['x'], $coords['y']);
        $village->setPlayer($player);

        $this->em->persist($village);
        $this->em->flush();

        return $village;
    }

    public function transferVillageOwnership(Player $player, Village $village): Village
    {
        $village->setPlayer($player);
        $this->em->persist($village);
        $this->em->flush();

        return $village;
    }

    public function getPlayerProfile(Player $player): array
    {
        return [
            'id'       => $player->getId(),
            'email'    => $player->getEmail(),
            'roles'    => $player->getRoles(),
            'villages' => array_map(fn($v) => [
                'id'   => $v->getId(),
                'name' => $v->getName(),
                'x'    => $v->getX(),
                'y'    => $v->getY(),
            ], $player->getVillages()->toArray()),
        ];
    }
}