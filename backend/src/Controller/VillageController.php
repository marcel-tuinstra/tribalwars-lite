<?php

namespace App\Controller;

use App\Entity\Village;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class VillageController extends AbstractController
{
    #[Route('/api/village/{id}', name: 'api_village', methods: ['GET'])]
    public function getVillage(Village $village): JsonResponse
    {
        return $this->json([
            'id'        => $village->getId(),
            'name'      => $village->getName(),
            'resources' => array_map(fn($resource) => [
                'category' => $resource->getCategory()->value,
                'amount'   => $resource->getAmount(),
            ], $village->getResources()->toArray()),
            'buildings' => array_map(fn($building) => [
                'category'        => $building->getCategory()->value(),
                'level'           => $building->getLevel(),
                'upgradeFinishAt' => $building->getUpgradeFinishAt()?->format(DATE_ATOM),
            ], $village->getBuildings()->toArray()),
            'troops'    => array_map(fn($troop) => [
                'role'             => $troop->getRole()->value(),
                'amount'           => $troop->getAmount(),
                'trainingFinishAt' => $troop->getTrainingFinishAt()?->format(DATE_ATOM),
                'queue'            => $troop->getTrainingQueue(),
            ], $village->getTroops()->toArray()),
        ]);
    }
}