<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use App\Service\PlayerService;
use App\Service\WorldMapService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/ping', methods: ['GET'])]
    public function ping(): JsonResponse
    {
        return $this->json(['status' => 'ok']);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?UserInterface $user): JsonResponse
    {
        if (null === $user) {
            return $this->json(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->create($user);

        return $this->json([
            'user'  => [
                'id'    => $user->getId(),
                'email' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ],
            'token' => $token,
        ]);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, PlayerService $playerService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name          = $data['name'] ?? null;
        $email         = $data['email'] ?? null;
        $plainPassword = $data['password'] ?? null;

        if (!$name || !$email || !$plainPassword) {
            return $this->json(['error' => 'Missing name, email or password'], 400);
        }

        $player         = new Player($name, $email);
        $hashedPassword = $passwordHasher->hashPassword($player, $plainPassword);
        $player->setPassword($hashedPassword);

        $em->persist($player);
        $em->flush();

        $playerService->createVillage($player);

        return $this->json(['status' => 'Player registered'], 201);
    }
}