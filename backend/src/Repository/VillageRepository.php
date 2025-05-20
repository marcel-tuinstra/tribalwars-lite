<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Village;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Village>
 */
class VillageRepository extends ServiceEntityRepository
{
    public const ALIAS = 'v';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Village::class);
    }

    public function findOneByCoordinates(int $x, int $y): ?Village
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->andX($queryBuilder, $x);
        $this->andY($queryBuilder, $y);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Returns all village coordinates as an array of ['x' => ..., 'y' => ...].
     * Optimized to only select x and y columns.
     *
     * @return array<int, array{x: int, y: int}>
     */
    public function findAllVillageForMapView(): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS)
            ->select(sprintf('%s.id', self::ALIAS))
            ->addSelect(sprintf('%s.x', self::ALIAS))
            ->addSelect(sprintf('%s.y', self::ALIAS))
            ->leftJoin(sprintf('%s.player', self::ALIAS), 'p')
            ->addSelect('p.id AS playerId')
            ->addSelect(sprintf('%s.name', self::ALIAS));

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * Returns all village coordinates as an array of ['x' => ..., 'y' => ...].
     * Optimized to only select x and y columns.
     *
     * @return array<int, array{x: int, y: int}>
     */
    public function findAllVillageCoordinates(): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS)
            ->select(sprintf('%s.id', self::ALIAS))
            ->addSelect(sprintf('%s.x', self::ALIAS))
            ->addSelect(sprintf('%s.y', self::ALIAS));

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function findAllBotVillages(): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->andBot($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllPlayerVillages(): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->andHuman($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllVillagesByPlayer(Player $player): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->andPlayer($queryBuilder, $player);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findVillagesInRange(int $centerX, int $centerY, int $range): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->where(sprintf('ABS(%s.x - :x) <= :range', self::ALIAS))
            ->andWhere(sprintf('ABS(%s.y - :y) <= :range', self::ALIAS))
            ->setParameters(['x' => $centerX, 'y' => $centerY, 'range' => $range]);

        return $queryBuilder->getQuery()->getResult();
    }

    private function andBot(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->andWhere(sprintf('%s.player IS NULL', self::ALIAS));
    }

    private function andHuman(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->andWhere(sprintf('%s.player IS NOT NULL', self::ALIAS));
    }

    public function andPlayer(QueryBuilder $queryBuilder, Player $player): void
    {
        $queryBuilder->andWhere(sprintf('%s.player = :player', self::ALIAS))
            ->setParameter('player', $player);
    }

    public function andX(QueryBuilder $queryBuilder, int $x): void
    {
        $queryBuilder->andWhere(sprintf('%s.x = :x', self::ALIAS))
            ->setParameter('x', $x);
    }

    public function andY(QueryBuilder $queryBuilder, int $y): void
    {
        $queryBuilder->andWhere(sprintf('%s.y = :y', self::ALIAS))
            ->setParameter('y', $y);
    }
}