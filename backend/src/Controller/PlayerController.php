<?php

namespace App\Controller;

use App\Entity\Player;
use App\Service\PlayerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    #[Route('/api/player/profile', name: 'api_player_profile', methods: ['GET'])]
    public function profile(PlayerService $playerService): JsonResponse
    {
        /** @var Player $player */
        $player = $this->getUser();

        return $this->json($playerService->getPlayerProfile($player));
    }
}