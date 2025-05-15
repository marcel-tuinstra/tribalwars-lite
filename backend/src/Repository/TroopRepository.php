<?php

namespace App\Repository;

use App\Entity\Building;
use App\Entity\Troop;
use App\Entity\Village;
use App\ValueObject\Troop\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Troop>
 */
class TroopRepository extends ServiceEntityRepository
{
    public const ALIAS = 'vt';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Troop::class);
    }

    public function getTroopByVillageAndRole(Village $village, Role $role): Building
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->andRole($queryBuilder, $role);
        $this->andVillage($queryBuilder, $village);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function andRole(QueryBuilder $queryBuilder, Role $role): void
    {
        $queryBuilder->andWhere(sprintf('%s.role = :role', self::ALIAS))
            ->setParameter('role', $role);
    }

    public function andVillage(QueryBuilder $queryBuilder, Village $village): void
    {
        $queryBuilder->andWhere(sprintf('%s.village = :village', self::ALIAS))
            ->setParameter('village', $village);
    }
}
