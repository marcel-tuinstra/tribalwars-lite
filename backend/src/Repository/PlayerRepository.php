<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public const ALIAS = 'p';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findOneByEmail(string $email): ?Player
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->andEmail($queryBuilder, $email);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function andEmail(QueryBuilder $queryBuilder, string $email): void
    {
        $queryBuilder->andWhere(sprintf('%s.email = :email', self::ALIAS))
            ->setParameter('email', $email);
    }
}
