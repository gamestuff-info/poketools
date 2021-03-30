<?php

namespace App\Repository;

use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Version|null find($id, $lockMode = null, $lockVersion = null)
 * @method Version|null findOneBy(array $criteria, array $orderBy = null)
 * @method Version[]    findAll()
 * @method Version[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersionRepository extends ServiceEntityRepository
{
    /**
     * @var string
     */
    private $defaultVersionSlug;

    /**
     * VersionRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param string $defaultVersionSlug
     */
    public function __construct(ManagerRegistry $registry, string $defaultVersionSlug)
    {
        parent::__construct($registry, Version::class);

        $this->defaultVersionSlug = $defaultVersionSlug;
    }

    /**
     * Get a list of all versions grouped by generation.
     *
     * The result is a multi-dimensional array.  The first level is keyed by
     * generation name, the second level is a list of versions in that generation.
     *
     * @return array
     */
    public function findAllVersionsGroupedByGeneration(): array
    {
        $qb = $this->createQueryBuilder('version');
        $qb->addSelect('version_group')->addSelect('generation')
            ->join('version.versionGroup', 'version_group')
            ->join('version_group.generation', 'generation')
            ->orderBy('version.position');
        $q = $qb->getQuery();
        /** @var Version[] $results */
        $results = $q->execute();

        $groupedResults = [];
        foreach ($results as $version) {
            $generation = $version->getVersionGroup()->getGeneration();
            $groupedResults[$generation->getName()][] = $version;
        }

        return $groupedResults;
    }

    /**
     * Find all version slugs
     *
     * @param string|null $versionGroup
     *
     * @return string[]
     */
    public function findAllSlugs(?string $versionGroup = null): array
    {
        $qb = $this->createQueryBuilder('version')
            ->select('version.slug')
            ->orderBy('version.position');
        if ($versionGroup !== null) {
            $qb->join('version.versionGroup', 'version_group')
                ->andWhere('version_group.slug = :versionGroup')
                ->setParameter('versionGroup', $versionGroup);
        }
        $q = $qb->getQuery();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @return Version
     */
    public function getDefaultVersion(): Version
    {
        return $this->findOneBy(['slug' => $this->defaultVersionSlug]);
    }
}
