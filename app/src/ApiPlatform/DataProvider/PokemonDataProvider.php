<?php


namespace App\ApiPlatform\DataProvider;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\EntityHydrator;
use App\Entity\EggGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\Version;
use App\Repository\PokemonRepository;

/**
 * Pokemon CollectionDataProvider
 */
class PokemonDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    use ExtensionAwareCollectionDataProviderTrait;

    public function __construct(
        private PokemonRepository $pokemonRepo,
        private EntityHydrator $entityHydrator,
        private iterable $collectionExtensions,
        private Version $defaultVersion,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        $qb = null;
        if (isset($filters['nature'])) {
            // Find Pokemon suitable to this nature
            $nature = $this->entityHydrator->hydrateEntity($filters['nature'], Nature::class);
            if (!$nature) {
                return [];
            }
            $qb = $this->pokemonRepo->findPokemonForNatureQb($nature);
        } elseif (isset($filters['eggGroups'])) {
            $eggGroups = $filters['eggGroups'];
            if (!is_array($eggGroups)) {
                $eggGroups = [$eggGroups];
            }
            foreach ($eggGroups as &$eggGroup) {
                $eggGroup = $this->entityHydrator->hydrateEntity($eggGroup, EggGroup::class);
                if (!$eggGroup) {
                    return [];
                }
            }
            unset($eggGroup);

            foreach ($eggGroups as $eggGroup) {
                if ($eggGroup->getSlug() === 'ditto') {
                    // Ditto can breed with everyone, so don't filter on egg groups.
                    unset($context['filters']['eggGroups']);
                    break;
                } elseif ($eggGroup->getSlug() === 'undiscovered') {
                    // Members of the undiscovered egg group cannot breed.
                    return [];
                }
            }
        } elseif (isset($filters['evolvesWithItemSlug'])) {
            $itemSlug = $filters['evolvesWithItemSlug'];
            $qb = $this->pokemonRepo->evolvesWithItemSlugQb($itemSlug);
        }
        if (!$qb) {
            $qb = $this->pokemonRepo->createQueryBuilder('pokemon');
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
        return $resourceClass === Pokemon::class;
    }
}
