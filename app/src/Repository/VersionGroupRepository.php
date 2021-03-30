<?php

namespace App\Repository;

use App\Entity\Feature;
use App\Entity\VersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method VersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method VersionGroup[]    findAll()
 * @method VersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VersionGroup::class);
    }

    /**
     * @return string[]
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('version_group');
        $qb->select('version_group.slug')
            ->orderBy('version_group.position');
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @param Feature|string|Feature[]|string[] $features
     *
     * @return VersionGroup[]
     */
    public function findWithFeatures(Feature|string|iterable $features): array
    {
        if (!is_iterable($features)) {
            $features = [$features];
        }

        $qb = $this->createQueryBuilder('version_group')
            ->join('version_group.features', 'features')
            ->orderBy('version_group.position');
        $featureObjects = [];
        $featureSlugs = [];
        foreach ($features as $feature) {
            if ($feature instanceof Feature) {
                $featureObjects[] = $feature;
            } elseif (is_string($feature)) {
                $featureSlugs[] = $feature;
            } else {
                throw new \LogicException('Invalid type passed to findWithFeature');
            }
        }
        if (!empty($featureObjects)) {
            $qb->orWhere('features IN (:featureObjects)')
                ->setParameter('featureObjects', $featureObjects);
        }
        if (!empty($featureSlugs)) {
            $qb->orWhere('features.slug IN (:featureSlugs)')
                ->setParameter('featureSlugs', $featureSlugs);
        }
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
