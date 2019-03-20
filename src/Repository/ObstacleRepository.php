<?php

namespace App\Repository;

use App\Entity\Obstacle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Obstacle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Obstacle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Obstacle[]    findAll()
 * @method Obstacle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObstacleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Obstacle::class);
    }
    public function getObstacles()
    {
        return $this->createQueryBuilder('o')
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return Obstacle[] Returns an array of Obstacle objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Obstacle
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
