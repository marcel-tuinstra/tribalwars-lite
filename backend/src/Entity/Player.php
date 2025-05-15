<?php

namespace App\Entity;

use App\Entity\Interface\IdentifiableInterface;
use App\Entity\Interface\TimestampableInterface;
use App\Entity\Trait\EqualsTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player implements IdentifiableInterface, PasswordAuthenticatedUserInterface, UserInterface, TimestampableInterface
{
    use EqualsTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, Village>
     */
    #[ORM\OneToMany(targetEntity: Village::class, mappedBy: 'player')]
    private Collection $villages;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'player')]
    private Collection $notifications;

    public function __construct(string $email)
    {
        $this->email = $email;

        $this->villages      = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    // Getters
    //////////////////////////////

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return Collection<int, Village>
     */
    public function getVillages(): Collection
    {
        return $this->villages;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function getRoles(): array
    {
        $roles   = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    // Setters
    //////////////////////////////

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    // Villages
    //////////////////////////////

    public function addVillage(Village $village): static
    {
        if (!$this->villages->contains($village)) {
            $this->villages->add($village);
            $village->setPlayer($this);
        }

        return $this;
    }

    public function removeVillage(Village $village): static
    {
        if ($this->villages->removeElement($village)) {
            // set the owning side to null (unless already changed)
            if ($village->getPlayer() === $this) {
                $village->setPlayer(null);
            }
        }

        return $this;
    }

    // Notifications
    //////////////////////////////

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setPlayer($this);
        }

        return $this;
    }


    // Derived Methods
    //////////////////////////////

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you had temporary sensitive data, clear it here
        // Example: $this->plainPassword = null;
    }
}
