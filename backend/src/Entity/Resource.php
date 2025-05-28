<?php

namespace App\Entity;

use App\Collection\BuildingCollection;
use App\Entity\Interface\IdentifiableInterface;
use App\Entity\Interface\TimestampableInterface;
use App\Entity\Trait\EqualsTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\ResourceRepository;
use App\ValueObject\Building\Category as BuildingCategory;
use App\ValueObject\Resource\Category;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ResourceRepository::class)]
class Resource implements IdentifiableInterface, TimestampableInterface
{
    use EqualsTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'resource:category')]
    private Category $category;

    #[ORM\Column]
    private int $amount = 250;

    #[ORM\ManyToOne(inversedBy: 'resources')]
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

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getVillage(): Village
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

    public function increaseAmount(int $amount): void
    {
        $this->amount += $amount;
    }

    // Derived Methods
    //////////////////////////////

    public function getResponsibleBuilding(): Building
    {
        $buildingCollection = new BuildingCollection($this->village->getBuildings()->toArray());

        return match ($this->category->value()) {
            Category::WOOD => $buildingCollection->getBuildingByCategory(BuildingCategory::lumberCamp()),
            Category::STONE => $buildingCollection->getBuildingByCategory(BuildingCategory::miningCamp()),
            Category::FOOD => $buildingCollection->getBuildingByCategory(BuildingCategory::mill()),
        };
    }
}
