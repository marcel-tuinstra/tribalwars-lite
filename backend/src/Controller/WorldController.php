<?php

namespace App\Controller;

use App\Entity\Player;
use App\Service\PlayerService;
use App\Service\WorldMapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class WorldController extends AbstractController
{
    public function __construct(private readonly WorldMapService $worldMapService)
    {

    }

    #[Route('/api/world/size', methods: ['GET'])]
    public function worldSize(): JsonResponse
    {
        return $this->json($this->worldMapService->getWorldSize());
    }

    #[Route('/api/world/village/{x}/{y}', methods: ['GET'])]
    public function villageAt(int $x, int $y): JsonResponse
    {
        $village = $this->worldMapService->getVillageAt($x, $y);

        if (!$village) {
            return $this->json(['error' => 'Village not found'], 404);
        }

        return $this->json([
            'id'     => $village->getId(),
            'name'   => $village->getName(),
            'player' => $village->getPlayer()?->getEmail(),
            'x'      => $village->getX(),
            'y'      => $village->getY(),
        ]);
    }

    #[Route('/api/world/map', methods: ['GET'])]
    public function visibleMap(WorldMapService $service): JsonResponse
    {
        /** @var Player $player */
        $player   = $this->getUser();
        $villages = $service->getVisibleMapForPlayer($player);

        return $this->json(array_map(fn($v) => [
            'id'     => $v->getId(),
            'name'   => $v->getName(),
            'x'      => $v->getX(),
            'y'      => $v->getY(),
            'player' => $v->getPlayer()?->getEmail(),
        ], $villages));
    }
}