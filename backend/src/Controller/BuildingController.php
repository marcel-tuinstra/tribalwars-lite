<?php

namespace App\Controller;

use App\Entity\Building;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class BuildingController extends AbstractController
{
    #[Route('/api/building/{id}/upgrade', name: 'api_building_upgrade', methods: ['POST'])]
    public function upgradeBuilding(Building $building, EntityManagerInterface $em): JsonResponse
    {
        if ($building->getUpgradeFinishAt() !== null) {
            return $this->json(['error' => 'Building already upgrading'], 400);
        }

        $building->startUpgrade(60); // 60s base time (tune later)
        $em->flush();

        return $this->json(['status' => 'Building upgrade started']);
    }
}