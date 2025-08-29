<?php

namespace App\Repository;

use App\Entity\CommandeDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeDetails>
 *
 * @method CommandeDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandeDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandeDetails[]    findAll()
 * @method CommandeDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeDetails::class);
    }

    /**
     * Exemple : récupérer toutes les lignes d'une commande donnée
     */
    public function findByCommande(int $commandeId): array
    {
        return $this->createQueryBuilder('cd')
            ->andWhere('cd.commande = :commandeId')
            ->setParameter('commandeId', $commandeId)
            ->orderBy('cd.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Exemple : récupérer le total payé pour une commande
     */
    public function getTotalPriceByCommande(int $commandeId): float
    {
        $qb = $this->createQueryBuilder('cd')
            ->select('SUM(cd.price * cd.quantity) as total')
            ->andWhere('cd.commande = :commandeId')
            ->setParameter('commandeId', $commandeId)
            ->getQuery()
            ->getSingleScalarResult();

        return (float)$qb;
    }

    // Ici tu peux ajouter d'autres requêtes spécifiques, par exemple :
    // - top articles
    // - top clients
    // - statistiques par période, etc.
}
