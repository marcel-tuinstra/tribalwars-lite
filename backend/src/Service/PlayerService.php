<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\Village;
use Doctrine\ORM\EntityManagerInterface;

class PlayerService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function createInitialVillage(Player $player, int $x, int $y): Village
    {
        $village = new Village('Village of ' . $player->getEmail(), $x, $y);
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