<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\Player;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/api/notifications', methods: ['GET'])]
    public function listNotifications(NotificationRepository $notificationRepository): JsonResponse
    {
        /** @var Player $player */
        $player        = $this->getUser();
        $notifications = $notificationRepository->findAllNotificationsByPlayer($player);

        $data = array_map(fn(Notification $n) => [
            'type'      => $n->getType(),
            'payload'   => $n->getPayload(),
            'createdAt' => $n->getCreatedAt()->format(DATE_ATOM),
        ], $notifications);

        return $this->json($data);
    }
}