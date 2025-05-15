<?php

namespace App\Entity;

use App\Entity\Interface\IdentifiableInterface;
use App\Entity\Interface\TimestampableInterface;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification implements IdentifiableInterface, TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    private Player $player;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column]
    private array $payload = [];

    public function __construct(Player $player, string $type)
    {
        $this->player = $player;
        $this->type   = $type;
    }

    // Getters
    //////////////////////////////

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    // Setters
    //////////////////////////////

    public function setPlayer(Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }
}
