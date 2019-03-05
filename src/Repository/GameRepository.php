<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function getGameForUserId($userId)
    {
        $query = $this->createQueryBuilder('g')
            ->where("(g.userId1 = :userId or g.userId2 = :userId) 
                                   and (g.status=:waitingStatus or g.status=:playStatus)")
            ->setParameter('userId', $userId)
            ->setParameter('playStatus', Game::STATUS_PLAY)
            ->setParameter('waitingStatus', Game::STATUS_WAITING)
            ->getQuery();
        return ($query->setMaxResults(1)->getOneOrNullResult());
    }
    public function getFreeGame()
    {
        $query = $this->createQueryBuilder('g')
            ->where('g.userId2 is NULL and g.status=:status')
            ->setParameter('status', Game::STATUS_WAITING)
            ->getQuery();
        return ($query->setMaxResults(1)->getOneOrNullResult());
    }
    // /**
    //  * @return Game[] Returns an array of Game objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Game
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
