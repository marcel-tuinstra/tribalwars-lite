<?php

namespace App\Controller;

use App\Collection\BuildingCollection;
use App\Entity\Troop;
use App\ValueObject\Building\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TroopController extends AbstractController
{
    #[Route('/api/troop/{id}/train', name: 'api_troop_train', methods: ['POST'])]
    public function trainTroops(Request $request, Troop $troop, EntityManagerInterface $em): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $amount  = $data['amount'] ?? 0;

        if ($troop->getTrainingFinishAt() !== null) {
            return $this->json(['error' => 'Troop already training'], 400);
        }

        $village            = $troop->getVillage();
        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        $barracks           = $buildingCollection->getBuildingByCategory(Category::barracks());

        $troop->startTraining($amount, 90, $barracks->getLevel());
        $em->flush();

        return $this->json(['status' => 'Troop training started']);
    }
}