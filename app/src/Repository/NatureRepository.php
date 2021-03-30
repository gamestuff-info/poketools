<?php

namespace App\Repository;

use App\Entity\Nature;
use App\Entity\Version;
use App\Entity\VersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Nature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nature[]    findAll()
 * @method Nature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nature::class);
    }

    /**
     * Find all for path generation
     *
     * @return string[]
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('nature');
        $qb->select('nature.slug');
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @return array{versionSlug: string, natureSlug: string, name: string}
     */
    public function findForSearchIndex(): array
    {
        $versionsWithNaturesQb = $this->getEntityManager()->createQueryBuilder();
        $versionsWithNaturesQb->select('version.slug versionSlug')
            ->from(Version::class, 'version')
            ->join('version.versionGroup', 'version_group')
            ->join('version_group.features', 'feature')
            ->where('feature.slug = \'natures\'');
        $versionsWithNaturesQ = $versionsWithNaturesQb->getQuery();
        $versionsWithNaturesQ->execute();
        $versionsWithNatures = $versionsWithNaturesQ->getArrayResult();

        $qb = $this->createQueryBuilder('nature');
        $qb->select('nature.slug natureSlug', 'nature.name name');
        $q = $qb->getQuery();
        $q->execute();
        $natures = $q->getArrayResult();

        $results = [];
        foreach ($versionsWithNatures as $version) {
            foreach ($natures as $nature) {
                $results[] = $version + $nature;
            }
        }

        return $results;
    }
}
