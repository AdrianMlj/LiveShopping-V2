<?php

namespace App\Repository;

use App\Entity\Rating;
use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    /**
     * @return array{avg: float|null, count: int}
     */
    public function getAvgAndCountForItem(Item $item): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('AVG(r.value) AS avg_rating, COUNT(r.id) AS cnt')
            ->andWhere('r.item = :item')
            ->setParameter('item', $item);

        $res = $qb->getQuery()->getOneOrNullResult();
        $avg = $res && $res['avg_rating'] !== null ? (float)$res['avg_rating'] : null;
        $cnt = $res ? (int)$res['cnt'] : 0;
        return ['avg' => $avg, 'count' => $cnt];
    }
}
