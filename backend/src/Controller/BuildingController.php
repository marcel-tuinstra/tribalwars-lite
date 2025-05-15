<?php

namespace App\Controller;

use App\Collection\ResourceCollection;
use App\Entity\Building;
use App\Service\ResourceService;
use App\ValueObject\Resource\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class BuildingController extends AbstractController
{
    #[Route('/api/building/{id}/upgrade', name: 'api_building_upgrade', methods: ['POST'])]
    public function upgradeBuilding(Building $building, ResourceService $resourceService, EntityManagerInterface $em): JsonResponse
    {
        if ($building->getUpgradeFinishAt() !== null) {
            return $this->json(['error' => 'Building already upgrading'], 400);
        }

        $village            = $building->getVillage();
        $resourceCollection = new ResourceCollection($village->getResources()->toArray());
        $cost               = $resourceService->calculateBuildingUpgradeCost($building->getCategory(), $building->getLevel() + 1);

        foreach ($cost as $resourceType => $amount) {
            $resource = $resourceCollection->getResourceByCategory(Category::fromString($resourceType));

            if ($resource->getAmount() < $amount) {
                return $this->json(['error' => "Not enough $resourceType"], 400);
            }
        }

        // Deduct resources
        foreach ($cost as $resourceType => $amount) {
            $resource = $resourceCollection->getResourceByCategory(Category::fromString($resourceType));
            $resource->decreaseAmount($amount);
        }

        // Start upgrade
        $building->startUpgrade(60);
        $em->flush();

        return $this->json(['status' => 'Building upgrade started']);
    }
}