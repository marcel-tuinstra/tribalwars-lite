<?php

namespace App\Controller;

use App\Entity\Village;
use App\Repository\VillageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class VillageController extends AbstractController
{
    public function __construct(private readonly VillageRepository $villageRepository)
    {
    }

    #[Route('/api/villages', name: 'api_villages', methods: ['GET'])]
    public function getVillages(#[CurrentUser] $player): JsonResponse
    {
        return $this->json($this->villageRepository->findAllVillagesByPlayer($player));
    }

    #[Route('/api/villages/all', name: 'api_villages_all', methods: ['GET'])]
    public function getVillagesAll(#[CurrentUser] $player): JsonResponse
    {
        return $this->json($this->villageRepository->findAllVillageForMapView());
    }

    #[Route('/api/village/{id}', name: 'api_village', methods: ['GET'])]
    public function getVillage(#[CurrentUser] $player, Village $village): JsonResponse
    {
        if (!$village->isOwnedBy($player)) {
            $this->json(['error' => 'Not your village'], 403);
        }

        return $this->json($village);
    }
}