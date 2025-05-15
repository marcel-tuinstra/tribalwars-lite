<?php

namespace App\Controller;

use App\Event\AttackLaunchedEvent;
use App\Repository\VillageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AttackController extends AbstractController
{
    #[Route('/api/attack', name: 'api_attack', methods: ['POST'])]
    public function attack(Request $request, VillageRepository $villageRepo, EventDispatcherInterface $eventDispatcher): JsonResponse
    {
        $data       = json_decode($request->getContent(), true);
        $attackerId = $data['attackerId'] ?? null;
        $defenderId = $data['defenderId'] ?? null;

        $attacker = $villageRepo->find($attackerId);
        $defender = $villageRepo->find($defenderId);

        if (!$attacker || !$defender) {
            return $this->json(['error' => 'Invalid village IDs'], 404);
        }

        $eventDispatcher->dispatch(new AttackLaunchedEvent($attacker, $defender));

        return $this->json(['status' => 'Attack dispatched']);
    }
}