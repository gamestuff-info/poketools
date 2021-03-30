<?php

namespace App\Repository;

use App\Entity\ItemInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemInVersionGroup[]    findAll()
 * @method ItemInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemInVersionGroupRepository extends ServiceEntityRepository implements SlugAndVersionInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemInVersionGroup::class);
    }

    /**
     * @return string[]
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('item_in_version_group');
        $qb->select('item_in_version_group.slug')
            ->distinct();
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @return array{versionSlug: string, itemSlug: string, name: string, icon: string|null}
     */
    public function findForSearchIndex(): array
    {
        $qb = $this->createQueryBuilder('item_in_version_group');
        $qb->select('item_in_version_group.slug itemSlug')
            ->addSelect('version.slug versionSlug')
            ->addSelect('item_in_version_group.name name')
            ->addSelect('item_in_version_group.icon icon')
            ->join('item_in_version_group.versionGroup', 'version_group')
            ->join('version_group.versions', 'version');
        $q = $qb->getQuery();
        $q->execute();

        return $q->getArrayResult();
    }

    /**
     * @param string $slug
     *
     * @return ItemInVersionGroup[]
     */
    public function findForAllVersions(string $slug): array
    {
        $qb = $this->createQueryBuilder('item');
        $qb->join('item.versionGroup', 'version_group')
            ->andWhere('item.slug = :slug')
            ->orderBy('version_group.position')
            ->setParameter('slug', $slug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByVersion(string $slug, Version|string $version): ?ItemInVersionGroup
    {
        $qb = $this->createQueryBuilder('item');
        $qb->join('item.versionGroup', 'version_group')
            ->andWhere('item.slug = :slug')
            ->setMaxResults(1)
            ->setParameter('version', $version)
            ->setParameter('slug', $slug);
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

    /**
     * @param Version $version
     * @param array $categories
     *
     * @return ItemInVersionGroup[]
     */
    public function findAllByVersionAndCategory(Version $version, array $categories): array
    {
        $qb = $this->createQueryBuilder('item');
        $qb->join('item.versionGroup', 'version_group')
            ->join('item.category', 'category')
            ->where(':version MEMBER OF version_group.versions')
            ->andWhere('category.slug IN (:categories)')
            ->addOrderBy('item.name')
            ->setParameter('version', $version)
            ->setParameter('categories', $categories);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
