<?php

namespace App\Repository;

use App\Entity\Type;
use App\Entity\TypeChart;
use App\Entity\Version;
use App\Entity\VersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeChart|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeChart|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeChart[]    findAll()
 * @method TypeChart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeChartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeChart::class);
    }

    /**
     * Find a type in a given type chart.
     *
     * @param string $typeSlug
     * @param Version $version
     *
     * @return Type|null
     */
    public function findTypeInTypeChart(string $typeSlug, Version $version): ?Type
    {
        // Find the applicable type chart.
        $typeChart = $this->findOneByVersion($version);
        if ($typeChart === null) {
            return null;
        }

        $type = null;
        foreach ($typeChart->getTypes() as $testType) {
            if ($testType->getSlug() === $typeSlug) {
                $type = $testType;
                break;
            }
        }

        return $type;
    }

    /**
     * @param VersionGroup|int $versionGroup
     *
     * @return TypeChart|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByVersionGroup(VersionGroup|int $versionGroup): ?TypeChart
    {
        $qb = $this->createQueryBuilder('type_chart');
        $qb->andWhere(':versionGroup MEMBER OF type_chart.versionGroups')
            ->setMaxResults(1)
            ->setParameter('versionGroup', $versionGroup);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
