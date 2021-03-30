<?php

namespace App\Repository;

use App\Entity\MoveInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MoveInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveInVersionGroup[]    findAll()
 * @method MoveInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveInVersionGroupRepository extends ServiceEntityRepository implements SlugAndVersionInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoveInVersionGroup::class);
    }

    /**
     * @return string[]
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('move_in_version_group');
        $qb->select('move_in_version_group.slug')
            ->distinct();
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @return array{versionSlug: string, moveSlug: string, name: string}
     */
    public function findForSearchIndex(): array
    {
        $qb = $this->createQueryBuilder('move_in_version_group');
        $qb->select('move_in_version_group.slug moveSlug')
            ->addSelect('version.slug versionSlug')
            ->addSelect('move_in_version_group.name name')
            ->join('move_in_version_group.versionGroup', 'version_group')
            ->join('version_group.versions', 'version');
        $q = $qb->getQuery();
        $q->execute();

        return $q->getArrayResult();
    }

    /**
     * @param string $slug
     *
     * @return MoveInVersionGroup[]
     */
    public function findForAllVersions(string $slug): array
    {
        $qb = $this->createQueryBuilder('move');
        $qb->join('move.versionGroup', 'version_group')
            ->andWhere('move.slug = :slug')
            ->orderBy('version_group.position')
            ->setParameter('slug', $slug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByVersion(string $slug, Version|string $version): ?MoveInVersionGroup
    {
        $qb = $this->createQueryBuilder('move');
        $qb->join('move.versionGroup', 'version_group')
            ->andWhere('move.slug = :slug')
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
