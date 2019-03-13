<?php

namespace App\Repository;

use App\Entity\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\ShipFactory;

/**
 * @method Ship|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ship|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ship[]    findAll()
 * @method Ship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ship::class);
    }

    public function getShipsForUser($gameId, $userId)
    {
        $ships = [];
        $shipsFromBase = $this->createQueryBuilder('s')
            ->andWhere('s.gameId = :gameId and s.userId = :userId')
            ->setParameter('gameId', $gameId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
        foreach($shipsFromBase as $ship) {

            $ships[] = shipFactory::createShip($ship->getName(), $ship->getAll());
        }
        return $ships;
    }
    public function getShipsForGame($gameId)
    {
        $shipsFromBase = $this->createQueryBuilder('s')
            ->andWhere('s.gameId = :gameId')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getResult();
        $ships = [];
        foreach($shipsFromBase as $ship) {
            $userId = $ship->getUserId();
            if (!isset($ships[$userId]))
                $ships[$userId] = [];

            $ships[$userId][] = shipFactory::createShip($ship->getName(), $ship->getAll());
        }
        return $ships;

    }
    // /**
    //  * @return Ship[] Returns an array of Ship objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ship
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
