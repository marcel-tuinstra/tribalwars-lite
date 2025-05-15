<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function createNotification(Player $player, string $type, array $payload = []): void
    {
        $notification = new Notification($player, $type);
        $notification->setPayload($payload);
        $this->em->persist($notification);
    }
}