<?php

namespace App\Entity;

use App\Entity\Interface\IdentifiableInterface;
use App\Entity\Interface\TimestampableInterface;
use App\Entity\Trait\EqualsTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\BuildingRepository;
use App\ValueObject\Building\Category;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
class Building implements IdentifiableInterface, TimestampableInterface
{
    use EqualsTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'building:category')]
    private Category $category;

    #[ORM\Column]
    private int $level = 0;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $upgradeFinishAt = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    #[ORM\JoinColumn(nullable: false)]
    private Village $village;

    public function __construct(Village $village, Category $category)
    {
        $this->village  = $village;
        $this->category = $category;
    }

    // Getters
    //////////////////////////////

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function getUpgradeFinishAt(): ?DateTimeImmutable
    {
        return $this->upgradeFinishAt;
    }

    public function getVillage(): ?Village
    {
        return $this->village;
    }

    // Setters
    //////////////////////////////

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    // Action Methods
    //////////////////////////////

    public function startUpgrade(int $baseDurationInSeconds): void
    {
        $efficiencyFactor = 1 - ($this->level * 0.05); // -5% per level
        $duration         = (int)max(10, $baseDurationInSeconds * $efficiencyFactor);

        $this->upgradeFinishAt = new DateTimeImmutable("+{$duration} seconds");
    }

    public function completeUpgrade(): void
    {
        $this->level++;
        $this->upgradeFinishAt = null;
    }

    // Derived Methods
    //////////////////////////////

    public function isUpgradeFinished(): bool
    {
        return $this->upgradeFinishAt !== null && $this->upgradeFinishAt <= new DateTimeImmutable();
    }
}
