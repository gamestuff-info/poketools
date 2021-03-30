<?php


namespace App\ApiPlatform\DataProvider;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\EntityHydrator;
use App\Entity\EncounterMethod;
use App\Entity\LocationArea;
use App\Entity\Version;
use App\Repository\EncounterMethodRepository;

/**
 * EncounterMethod CollectionDataProvider
 */
class EncounterMethodDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    use ExtensionAwareCollectionDataProviderTrait;

    public function __construct(
        private EncounterMethodRepository $encounterMethodRepo,
        private EntityHydrator $entityHydrator,
        private iterable $collectionExtensions,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        if (isset($filters['locationArea'])) {
            $areaId = $filters['locationArea'];
            // Find methods by which Pokemon can be encountered in this area.
            $area = $this->entityHydrator->hydrateEntityOrId($areaId, LocationArea::class);
            if (!$area) {
                return [];
            }
            $qb = $this->encounterMethodRepo->findForAreaQb($area);
        } elseif (isset($filters['version'])) {
            $version = $this->entityHydrator->hydrateEntityOrId($filters['version'], Version::class);
            $qb = $this->encounterMethodRepo->findForVersionQb($version);
        } else {
            $qb = $this->encounterMethodRepo->createQueryBuilder('encounter_method');
        }
        $nameGenerator = new QueryNameGenerator();

        return $this->applyExtensions(
                $this->collectionExtensions,
                $qb,
                $nameGenerator,
                $resourceClass,
                $operationName,
                $context
            )
            ?? $qb->getQuery()->getResult();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === EncounterMethod::class;
    }
}
