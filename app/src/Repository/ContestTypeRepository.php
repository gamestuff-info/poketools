<?php

namespace App\Repository;

use App\Entity\ContestType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContestType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContestType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContestType[]    findAll()
 * @method ContestType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContestTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContestType::class);
    }
}
