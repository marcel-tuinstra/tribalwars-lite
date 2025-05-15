<?php

namespace App\Controller;

use App\Collection\BuildingCollection;
use App\Collection\ResourceCollection;
use App\Entity\Troop;
use App\Service\ResourceService;
use App\ValueObject\Building\Category as BuildingCategory;
use App\ValueObject\Resource\Category as ResourceCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TroopController extends AbstractController
{
    #[Route('/api/troop/{id}/train', name: 'api_troop_train', methods: ['POST'])]
    public function trainTroops(Request $request, Troop $troop, ResourceService $resourceService, EntityManagerInterface $em): JsonResponse
    {
        $data   = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;

        if ($troop->getTrainingFinishAt() !== null) {
            return $this->json(['error' => 'Troop already training'], 400);
        }

        $village            = $troop->getVillage();
        $resourceCollection = new ResourceCollection($village->getResources()->toArray());

        $farmCap    = $resourceService->getFarmCap($village);
        $population = $resourceCollection->getResourceByCategory(ResourceCategory::population());

        if ($population->getAmount() >= $farmCap) {
            return $this->json(['error' => 'Farm cap reached'], 400);
        }

        $cost = $resourceService->calculateTroopTrainingCost($troop->getRole(), $amount);

        foreach ($cost as $resourceType => $amountRequired) {
            $resource = $resourceCollection->getResourceByCategory(ResourceCategory::fromString($resourceType));

            if ($resource->getAmount() < $amountRequired) {
                return $this->json(['error' => "Not enough $resourceType"], 400);
            }
        }

        // Deduct resources
        foreach ($cost as $resourceType => $amountRequired) {
            $resource = $resourceCollection->getResourceByCategory(ResourceCategory::fromString($resourceType));
            $resource->decreaseAmount($amountRequired);
        }

        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        $barracks           = $buildingCollection->getBuildingByCategory(BuildingCategory::barracks());

        // Start training
        $troop->startTraining($amount, 90, $barracks->getLevel());
        $em->flush();

        return $this->json(['status' => 'Troop training started']);
    }
}