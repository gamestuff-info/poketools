<?php

namespace App\Repository;

use App\Entity\TimeOfDay;
use App\Entity\VersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TimeOfDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeOfDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeOfDay[]    findAll()
 * @method TimeOfDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeOfDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeOfDay::class);
    }

    /**
     * @param VersionGroup|int $versionGroup
     *
     * @return QueryBuilder
     */
    public function findForVersionGroupQb(VersionGroup|int $versionGroup): QueryBuilder
    {
        $qb = $this->createQueryBuilder('time_of_day');
        $qb->join('time_of_day.generation', 'generation')
            ->andWhere(':versionGroup MEMBER OF generation.versionGroups')
            ->setParameter('versionGroup', $versionGroup);

        return $qb;
    }
}
