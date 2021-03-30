<?php

namespace App\Repository;

use App\Entity\LocationInVersionGroup;
use App\Entity\RegionInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use LogicException;

/**
 * @method LocationInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationInVersionGroup[]    findAll()
 * @method LocationInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationInVersionGroupRepository extends MaterializedPathRepository implements ServiceEntityRepositoryInterface, SlugAndVersionInterface
{
    /**
     * LocationInVersionGroupRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = LocationInVersionGroup::class;
        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass($entityClass);

        if ($manager === null) {
            throw new LogicException(
                sprintf(
                    'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                    $entityClass
                )
            );
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }

    /**
     * @return string[]
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('location_in_version_group');
        $qb->select('location_in_version_group.slug')
            ->distinct();
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @return array{versionSlug: string, locationSlug: string, name: string}
     */
    public function findForSearchIndex(): array
    {
        $qb = $this->createQueryBuilder('location_in_version_group');
        $qb->select('location_in_version_group.slug locationSlug')
            ->addSelect('version.slug versionSlug')
            ->addSelect('location_in_version_group.name name')
            ->join('location_in_version_group.versionGroup', 'version_group')
            ->join('version_group.versions', 'version');
        $q = $qb->getQuery();
        $q->execute();

        return $q->getArrayResult();
    }

    /**
     * @param string $slug
     *
     * @return LocationInVersionGroup[]
     */
    public function findForAllVersions(string $slug): array
    {
        $qb = $this->createQueryBuilder('location');
        $qb->join('location.versionGroup', 'version_group')
            ->andWhere('location.slug = :slug')
            ->orderBy('version_group.position')
            ->setParameter('slug', $slug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param RegionInVersionGroup $region
     *
     * @return LocationInVersionGroup[]
     */
    public function findByRegion(RegionInVersionGroup $region): array
    {
        $qb = $this->createQueryBuilder('location');
        $qb->andWhere('location.region = :region')
            ->setParameter('region', $region);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @inheritDoc
     *
     * @return LocationInVersionGroup|null
     */
    public function findOneByVersion(string $slug, Version|string $version)
    {
        $qb = $this->createQueryBuilder('location');
        $qb->join('location.versionGroup', 'version_group')
            ->andWhere('location.slug = :slug')
            ->setParameter('slug', $slug)
            ->setParameter('version', $version);
        if ($version instanceof Version) {
            $qb->andWhere(':version MEMBER OF version_group.versions');
        } else {
            $qb->join('version_group.versions', 'version')
                ->andWhere('version.slug = :version');
        }

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
