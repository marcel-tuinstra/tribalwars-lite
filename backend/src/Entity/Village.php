<?php

namespace App\Entity;

use App\Entity\Interface\IdentifiableInterface;
use App\Entity\Interface\TimestampableInterface;
use App\Entity\Trait\EqualsTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\VillageRepository;
use App\ValueObject\Building\Category as BuildingCategory;
use App\ValueObject\Resource\Category as ResourceCategory;
use App\ValueObject\Troop\Role as TroopRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'village')]
#[ORM\UniqueConstraint(name: 'village_unique_coordinates', columns: ['x', 'y'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: VillageRepository::class)]
class Village implements IdentifiableInterface, TimestampableInterface
{
    use EqualsTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    #[ORM\Column]
    private int $x;

    #[ORM\Column]
    private int $y;

    #[ORM\ManyToOne(inversedBy: 'villages')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Player $player = null;

    /**
     * @var Collection<int, Building>
     */
    #[ORM\OneToMany(targetEntity: Building::class, mappedBy: 'village', cascade: ['persist'])]
    private Collection $buildings;

    /**
     * @var Collection<int, Resource>
     */
    #[ORM\OneToMany(targetEntity: Resource::class, mappedBy: 'village', cascade: ['persist'])]
    private Collection $resources;

    /**
     * @var Collection<int, Troop>
     */
    #[ORM\OneToMany(targetEntity: Troop::class, mappedBy: 'village', cascade: ['persist'])]
    private Collection $troops;

    public function __construct(string $name, int $x, int $y)
    {
        $this->name = $name;
        $this->x    = $x;
        $this->y    = $y;

        // Buildings
        $this->buildings = new ArrayCollection();
        foreach (BuildingCategory::getValuesAsObjects() as $category) {
            $building = new Building($this, $category);
            $this->addBuilding($building);
        }

        // Resources
        $this->resources = new ArrayCollection();
        foreach (ResourceCategory::getValuesAsObjects() as $category) {
            $resource = new Resource($this, $category);
            $this->addResource($resource);
        }

        // Troops
        $this->troops = new ArrayCollection();
        foreach (TroopRole::getValuesAsObjects() as $role) {
            $troop = new Troop($this, $role);
            $this->addTroop($troop);
        }
    }

    // Getters
    //////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @return Collection<int, Building>
     */
    public function getBuildings(): Collection
    {
        return $this->buildings;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    /**
     * @return Collection<int, Troop>
     */
    public function getTroops(): Collection
    {
        return $this->troops;
    }

    // Setters
    //////////////////////////////

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    // Buildings
    //////////////////////////////

    public function addBuilding(Building $building): static
    {
        if (!$this->buildings->contains($building)) {
            $this->buildings->add($building);
        }

        return $this;
    }

    // Resources
    //////////////////////////////

    public function addResource(Resource $resource): static
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
        }

        return $this;
    }

    // Troops
    //////////////////////////////

    public function addTroop(Troop $troop): static
    {
        if (!$this->troops->contains($troop)) {
            $this->troops->add($troop);
        }

        return $this;
    }

    // Derived Methods
    //////////////////////////////

    public function isHuman(): bool
    {
        return $this->player === null;
    }

    public function isOwnedBy(Player $player): bool
    {
        return Player::equals($this->player, $player);
    }
}
