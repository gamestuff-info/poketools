<?php

namespace App\Repository;

use App\Entity\AbilityInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AbilityInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbilityInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbilityInVersionGroup[]    findAll()
 * @method AbilityInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbilityInVersionGroupRepository extends ServiceEntityRepository implements SlugAndVersionInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbilityInVersionGroup::class);
    }

    /**
     * @return string[]
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('ability_in_version_group');
        $qb->select('ability_in_version_group.slug')
            ->distinct();
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @return array{versionSlug: string, abilitySlug: string, name: string}
     */
    public function findForSearchIndex(): array
    {
        $qb = $this->createQueryBuilder('ability_in_version_group');
        $qb->select('ability_in_version_group.slug abilitySlug')
            ->addSelect('version.slug versionSlug')
            ->addSelect('ability_in_version_group.name name')
            ->join('ability_in_version_group.versionGroup', 'version_group')
            ->join('version_group.versions', 'version');
        $q = $qb->getQuery();
        $q->execute();

        return $q->getArrayResult();
    }

    /**
     * @param string $slug
     *
     * @return AbilityInVersionGroup[]
     */
    public function findForAllVersions(string $slug): array
    {
        $qb = $this->createQueryBuilder('ability_in_version_group');
        $qb->join('ability_in_version_group.versionGroup', 'version_group')
            ->andWhere('ability_in_version_group.slug = :slug')
            ->orderBy('version_group.position')
            ->setParameter('slug', $slug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param string $slug
     * @param Version|string $version
     *
     * @return AbilityInVersionGroup|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByVersion(string $slug, Version|string $version): ?AbilityInVersionGroup
    {
        $qb = $this->createQueryBuilder('ability_in_version_group');
        $qb->join('ability_in_version_group.versionGroup', 'version_group')
            ->andWhere('ability_in_version_group.slug = :slug');
        if ($version instanceof Version) {
            $qb->andWhere(':version MEMBER OF version_group.versions');
        } else {
            $qb->join('version_group.versions', 'version')
                ->andWhere('version.slug = :version');
        }

        $q = $qb->getQuery();
        $q->execute(
            [
                'slug' => $slug,
                'version' => $version,
            ]
        );

        return $q->getOneOrNullResult();
    }
}
