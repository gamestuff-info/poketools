<?php


namespace App\ApiPlatform\DataProvider;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\EntityHydrator;
use App\Entity\TimeOfDay;
use App\Entity\VersionGroup;
use App\Repository\TimeOfDayRepository;

/**
 * TimeOfDay DataProvider
 */
class TimeOfDayDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    use ExtensionAwareCollectionDataProviderTrait;

    public function __construct(
        private TimeOfDayRepository $timeOfDayRepo,
        private EntityHydrator $entityHydrator,
        private iterable $collectionExtensions,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        if (isset($filters['versionGroup'])) {
            $versionGroup = $this->entityHydrator->hydrateEntityOrId($filters['versionGroup'], VersionGroup::class);
            $qb = $this->timeOfDayRepo->findForVersionGroupQb($versionGroup);
        } else {
            $qb = $this->timeOfDayRepo->createQueryBuilder('time_of_day');
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
        return $resourceClass === TimeOfDay::class;
    }
}
