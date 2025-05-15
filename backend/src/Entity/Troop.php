<?php

namespace App\Entity;

use App\Entity\Interface\IdentifiableInterface;
use App\Entity\Interface\TimestampableInterface;
use App\Entity\Trait\EqualsTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\TroopRepository;
use App\ValueObject\Troop\Power;
use App\ValueObject\Troop\Role;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: TroopRepository::class)]
class Troop implements IdentifiableInterface, TimestampableInterface
{
    use EqualsTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'troop:role')]
    private Role $role;

    private Power $power;

    #[ORM\Column]
    private int $amount = 0;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $trainingFinishAt = null;

    #[ORM\Column]
    private int $trainingQueue = 0;

    #[ORM\ManyToOne(inversedBy: 'troops')]
    #[ORM\JoinColumn(nullable: false)]
    private Village $village;

    public function __construct(Village $village, Role $role)
    {
        $this->village = $village;
        $this->role    = $role;
        $this->power   = new Power($role);
    }

    // Getters
    //////////////////////////////

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getPower(): Power
    {
        return $this->power;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getTrainingQueue(): int
    {
        return $this->trainingQueue;
    }

    public function getTrainingFinishAt(): ?DateTimeImmutable
    {
        return $this->trainingFinishAt;
    }

    public function getVillage(): ?Village
    {
        return $this->village;
    }

    // Setters
    //////////////////////////////

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    // Action Methods
    //////////////////////////////

    public function decreaseAmount(int $amount): void
    {
        $this->amount = max(0, $this->amount - $amount);
    }

    public function startTraining(int $amount, int $baseDurationInSeconds, int $barracksLevel): void
    {
        $efficiencyFactor = 1 - ($barracksLevel * 0.05);
        $duration         = (int)max(10, $baseDurationInSeconds * $efficiencyFactor);

        $this->trainingFinishAt = new DateTimeImmutable("+{$duration} seconds");
        $this->trainingQueue    = $amount;
    }

    public function completeTraining(): void
    {
        $this->amount           += $this->trainingQueue;
        $this->trainingQueue    = 0;
        $this->trainingFinishAt = null;
    }

    // Derived Methods
    //////////////////////////////

    public function isTrainingFinished(): bool
    {
        return $this->trainingFinishAt !== null && $this->trainingFinishAt <= new DateTimeImmutable();
    }

}
