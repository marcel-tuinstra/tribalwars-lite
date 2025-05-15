<?php

namespace App\Entity\Trait;

use App\Utility\DateTimeUtility;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?DateTimeImmutable $deletedAt = null;

    // Getters
    //////////////////////////////

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    // Setters
    //////////////////////////////

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = DateTimeUtility::nowUtcAsImmutable();

        return $this;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): static
    {
        $this->updatedAt = DateTimeUtility::nowUtc();

        return $this;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt = null): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    // Derived Methods
    //////////////////////////////

    public function isDeleted(): bool
    {
        return $this->deletedAt instanceof DateTimeImmutable;
    }
}