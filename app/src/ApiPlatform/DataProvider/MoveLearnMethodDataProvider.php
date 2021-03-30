<?php


namespace App\ApiPlatform\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\EntityHydrator;
use App\Entity\MoveInVersionGroup;
use App\Entity\MoveLearnMethod;
use App\Entity\Pokemon;
use App\Repository\MoveLearnMethodRepository;

/**
 * MoveLearnMethod CollectionDataProvider
 */
class MoveLearnMethodDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    use ExtensionAwareCollectionDataProviderTrait;

    public function __construct(
        private MoveLearnMethodRepository $moveLearnMethodRepo,
        private EntityHydrator $entityHydrator,
        private iterable $collectionExtensions,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        if (isset($filters['move'])) {
            // Find methods by which Pokemon can learn this move.
            $move = $this->entityHydrator->hydrateEntityOrId($filters['move'], MoveInVersionGroup::class);
            if (!$move) {
                return [];
            }
            $qb = $this->moveLearnMethodRepo->findUsedMethodsForMoveQb($move);
        } elseif ($filters['pokemon']) {
            // Find methods a Pokemon can use to learn moves.
            $pokemon = $this->entityHydrator->hydrateEntityOrId($filters['pokemon'], Pokemon::class);
            if (!$pokemon) {
                return [];
            }
            $qb = $this->moveLearnMethodRepo->findUsedMethodsForPokemonQb($pokemon);
        } else {
            $qb = $this->moveLearnMethodRepo->createQueryBuilder('move_learn_method');
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
        return $resourceClass === MoveLearnMethod::class;
    }
}
