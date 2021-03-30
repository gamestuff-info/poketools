<?php

namespace App\Repository;

use App\Entity\PokemonMoveMachine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonMoveMachine|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonMoveMachine|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonMoveMachine[]    findAll()
 * @method PokemonMoveMachine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonMoveMachineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonMoveMachine::class);
    }

//    /**
//     * @return PokemonMoveMachine[] Returns an array of PokemonMoveMachine objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PokemonMoveMachine
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
