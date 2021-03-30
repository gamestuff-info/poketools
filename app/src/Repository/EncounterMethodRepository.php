<?php

namespace App\Repository;

use App\Entity\Encounter;
use App\Entity\EncounterMethod;
use App\Entity\LocationArea;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EncounterMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method EncounterMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method EncounterMethod[]    findAll()
 * @method EncounterMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncounterMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EncounterMethod::class);
    }

    /**
     * @param Version|int $version
     *
     * @return QueryBuilder
     */
    public function findForVersionQb(Version|int $version): QueryBuilder
    {
        $qb = $this->createQueryBuilder('encounter_method');
        $qb->distinct()
            ->join(Encounter::class, 'encounter', 'WITH', 'encounter_method = encounter.method')
            ->andWhere('encounter.version = :version')
            ->orderBy('encounter_method.position')
            ->setParameter('version', $version);

        return $qb;
    }

    /**
     * Find all methods that can cause an encounter in an area
     *
     * @param LocationArea|int $area
     *
     * @return QueryBuilder
     */
    public function findForAreaQb(LocationArea|int $area): QueryBuilder
    {
        $qb = $this->createQueryBuilder('encounter_method');
        $qb->distinct()
            ->join(Encounter::class, 'encounter', 'WITH', 'encounter_method = encounter.method')
            ->andWhere('encounter.locationArea = :area')
            ->orderBy('encounter_method.position')
            ->setParameter('area', $area);

        return $qb;
    }
}
