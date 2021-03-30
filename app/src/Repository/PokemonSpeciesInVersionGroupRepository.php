<?php

namespace App\Repository;

use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonSpeciesInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSpeciesInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSpeciesInVersionGroup[] findAll()
 * @method PokemonSpeciesInVersionGroup[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSpeciesInVersionGroupRepository extends ServiceEntityRepository implements SlugAndVersionInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonSpeciesInVersionGroup::class);
    }

    /**
     * @return string[]
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('species');
        $qb->select('species.slug')
            ->distinct();
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @param string $slug
     *
     * @return PokemonSpeciesInVersionGroup[]
     */
    public function findForAllVersions(string $slug): array
    {
        $qb = $this->createQueryBuilder('species');
        $qb->join('species.versionGroup', 'version_group')
            ->andWhere('species.slug = :slug')
            ->orderBy('version_group.position')
            ->setParameter('slug', $slug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByVersion(string $slug, Version|string $version): ?PokemonSpeciesInVersionGroup
    {
        $qb = $this->createQueryBuilder('species');
        $qb->join('species.versionGroup', 'version_group')
            ->andWhere('species.slug = :slug')
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
