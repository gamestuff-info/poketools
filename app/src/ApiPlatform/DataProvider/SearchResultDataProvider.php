<?php


namespace App\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\ArrayPaginator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\Entity\SearchResult;
use App\ApiPlatform\EntityHydrator;
use App\Entity\AbilityInVersionGroup;
use App\Entity\EntityHasNameInterface;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationInVersionGroup;
use App\Entity\MoveInVersionGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\Type;
use App\Entity\Version;
use App\Search\Finder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * SearchResult DataProvider
 */
class SearchResultDataProvider implements ContextAwareCollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private Finder $finder,
        private EntityHydrator $entityHydrator,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        if (!isset($filters['q'])) {
            throw new BadRequestHttpException('q required');
        }
        $q = $filters['q'];
        if (isset($filters['version'])) {
            $version = $this->entityHydrator->hydrateEntity($filters['version'], Version::class);
            if (!$version) {
                return [];
            }
        } else {
            $version = null;
        }
        $page = $filters['page'] ?? 1;
        $itemsPerPage = $filters['itemsPerPage'] ?? 10;
        $resultEntities = $this->finder->search($q, $version);
        $results = [];
        foreach ($resultEntities as $resultEntity) {
            $results[] = (new SearchResult())
                ->setType($this->getEntityType($resultEntity))
                ->setId($resultEntity->getId())
                ->setResult($resultEntity)
                ->setLabel($this->getEntityLabel($resultEntity));
        }

        return new ArrayPaginator($results, ($page - 1) * $itemsPerPage, $itemsPerPage);
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        // Not supported
        throw new NotFoundHttpException();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === SearchResult::class;
    }

    private function getEntityType(object $entity): string
    {
        return match (get_class($entity)) {
            Pokemon::class => 'pokemon',
            MoveInVersionGroup::class => 'move',
            Type::class => 'type',
            ItemInVersionGroup::class => 'item',
            LocationInVersionGroup::class => 'location',
            Nature::class => 'nature',
            AbilityInVersionGroup::class => 'ability',
            default => throw new \ValueError('Bad search result class: '.get_class($entity)),
        };
    }

    private function getEntityLabel(object $entity): string
    {
        if (is_a($entity, EntityHasNameInterface::class)) {
            return $entity->getName() ?? '';
        }

        return '';
    }
}
