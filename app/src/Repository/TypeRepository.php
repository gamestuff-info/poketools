<?php

namespace App\Repository;

use App\Entity\Type;
use App\Entity\TypeChart;
use App\Entity\TypeEfficacy;
use App\Entity\VersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Type|null find($id, $lockMode = null, $lockVersion = null)
 * @method Type|null findOneBy(array $criteria, array $orderBy = null)
 * @method Type[]    findAll()
 * @method Type[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    /**
     * Find all type slugs
     *
     * @return string[]
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('pokemon_type');
        $qb->select('pokemon_type.slug');
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @return array{versionSlug: string, typeSlug: string, name: string}
     */
    public function findForSearchIndex(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('version.slug versionSlug')
            ->addSelect('pokemon_type.slug typeSlug')
            ->addSelect('pokemon_type.name')
            ->distinct()
            ->from(VersionGroup::class, 'version_group')
            ->join(TypeChart::class, 'type_chart', 'WITH', 'version_group MEMBER OF type_chart.versionGroups')
            ->join('type_chart.efficacies', 'efficacies')
            ->join('efficacies.attackingType', 'pokemon_type')
            ->join('version_group.versions', 'version');
        $q = $qb->getQuery();
        $q->execute();

        return $q->getArrayResult();
    }

    /**
     * @param string $slug
     *
     * @return Type[]
     */
    public function findVersionGroupsWithType(string $slug): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('version_group')
            ->from(VersionGroup::class, 'version_group')
            ->join(TypeChart::class, 'type_chart', 'WITH', 'version_group MEMBER OF type_chart.versionGroups')
            ->join('type_chart.efficacies', 'efficacies')
            ->join('efficacies.attackingType', 'pokemon_type')
            ->where('pokemon_type.slug = :slug')
            ->orderBy('version_group.position')
            ->setParameter('slug', $slug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
