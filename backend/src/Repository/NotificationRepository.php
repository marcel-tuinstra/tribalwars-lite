<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    private const ALIAS = 'n';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findAllNotificationsByPlayer(Player $player): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->andPlayer($queryBuilder, $player);

        $queryBuilder->orderBy(sprintf('%s.createdAt', self::ALIAS), 'DESC');
        $queryBuilder->setMaxResults(50);

        return $queryBuilder->getQuery()->getResult();
    }

    private function andPlayer(QueryBuilder $queryBuilder, Player $player): void
    {
        $queryBuilder->andWhere(sprintf('%s.player = :player', self::ALIAS))
            ->setParameter('player', $player);
    }
}
