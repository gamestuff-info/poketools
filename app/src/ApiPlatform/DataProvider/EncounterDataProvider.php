<?php


namespace App\ApiPlatform\DataProvider;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Encounter;
use App\Repository\EncounterRepository;

/**
 * Encounter DataProvider
 */
class EncounterDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    use ExtensionAwareCollectionDataProviderTrait;

    const ALLOW_NO_PAGINATION_FILTERS = ['pokemon', 'locationArea'];

    public function __construct(
        private EncounterRepository $encounterRepo,
        private iterable $collectionExtensions,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        if (isset($filters['pagination']) && !self::allowNoPagination($filters)) {
            // Pagination required
            unset($context['filters']['pagination']);
        }
        $qb = $this->encounterRepo->createQueryBuilder('encounter');
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
        return $resourceClass === Encounter::class;
    }

    /**
     * Returns true if a required filter is set and has a value.
     *
     * @param array $filters
     *
     * @return bool
     */
    static private function allowNoPagination(array $filters): bool
    {
        foreach (self::ALLOW_NO_PAGINATION_FILTERS as $requiredFilter) {
            if (isset($filters[$requiredFilter]) && $filters[$requiredFilter]) {
                // A required filter is used and has a value.
                return true;
            }
        }

        return false;
    }
}
