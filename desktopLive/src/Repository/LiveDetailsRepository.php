<?php

namespace App\Repository;

use App\Entity\LiveDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LiveDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LiveDetails::class);
    }

    /**
     * Récupère les items liés à un live avec leur dernier prix
     */
    public function findItemsByLive(int $liveId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT i.id_item AS idItem,
               i.name_item AS nameItem,
               i.images AS images,
               p.price AS price
        FROM live_details ld
        JOIN item i ON ld.id_item = i.id_item
        LEFT JOIN price_items p 
               ON p.id_item = i.id_item
               AND p.date_price = (
                   SELECT MAX(p2.date_price)
                   FROM price_items p2
                   WHERE p2.id_item = i.id_item
               )
        WHERE ld.id_live = :liveId
    ";


        $result = $conn->executeQuery($sql, ['liveId' => $liveId]);

        return $result->fetchAllAssociative(); // ✅ compatible DBAL 3
    }

}
