<?php


namespace App\ApiPlatform\DataProvider;


use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\Entity\PokemonStat;
use App\ApiPlatform\EntityHydrator;
use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * PokemonStat CollectionDataProvider and ItemDataProvider
 */
class PokemonStatDataProvider implements ContextAwareCollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private PokemonRepository $pokemonRepo,
        private EntityHydrator $entityHydrator,
    ) {
    }

    const STAT_SLUGS = [
        'hp',
        'attack',
        'defense',
        'special-attack',
        'special-defense',
        'special',
        'speed',
    ];

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        if (!isset($filters['pokemon'])) {
            throw new NotFoundHttpException('Pokemon required.');
        }
        $pokemon = $this->entityHydrator->hydrateEntity($filters['pokemon'], Pokemon::class);
        if (!$pokemon) {
            return [];
        }

        $percentiles = $this->pokemonRepo->calcStatPercentiles($pokemon);
        $results = [];
        foreach (self::STAT_SLUGS as $statSlug) {
            /** @var \App\Entity\PokemonStat|null $statValue */
            $statValue = $pokemon->getStat($statSlug);
            if (!$statValue || !$statValue->getBaseValue()) {
                continue;
            }
            $results[] = (new PokemonStat())->setPokemon($pokemon)
                ->setStat($statSlug)
                ->setBaseValue($statValue->getBaseValue())
                ->setPercentile($percentiles[$statSlug]);
        }
        $results[] = (new PokemonStat())
            ->setPokemon($pokemon)
            ->setStat('total')
            ->setBaseValue($pokemon->getStatTotal())
            ->setPercentile($percentiles['total']);

        return $results;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        // Not supported
        throw new NotFoundHttpException();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === PokemonStat::class;
    }
}
