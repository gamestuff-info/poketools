<?php


namespace App\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\Entity\PokemonEvolutionTree;
use App\ApiPlatform\EntityHydrator;
use App\CommonMark\VersionAwareCommonMarkFactory;
use App\Entity\Pokemon;
use App\Entity\PokemonEvolutionCondition;
use App\Entity\Version;
use App\Repository\PokemonRepository;
use League\CommonMark\MarkdownConverterInterface;

/**
 * PokemonEvolutionTree Data Provider
 */
class PokemonEvolutionTreeDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private PokemonRepository $pokemonRepo,
        private EntityHydrator $entityHydrator,
        private VersionAwareCommonMarkFactory $commonMarkFactory,
    ) {
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $pokemon = $this->entityHydrator->hydrateEntity($id, Pokemon::class);
        if (!$pokemon) {
            return null;
        }
        $evolutionRoot = $this->pokemonRepo->findEvolutionRoot($pokemon);
        $version = self::resolveVersion($context['_useVersion'], $evolutionRoot);
        $commonMarkConverter = $this->commonMarkFactory->getForVersion($version);

        return $this->buildEvolutionTree($evolutionRoot, $version, $commonMarkConverter);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === PokemonEvolutionTree::class;
    }

    private function buildEvolutionTree(
        Pokemon $pokemon,
        Version $version,
        MarkdownConverterInterface $commonMarkConverter
    ): PokemonEvolutionTree {
        $tree = new PokemonEvolutionTree();
        $tree->setPokemon($pokemon);

        // Conditions: Trigger > list of condition labels
        /** @var PokemonEvolutionCondition $evolutionCondition */
        foreach ($pokemon->getEvolutionConditions() as $evolutionCondition) {
            $trigger = $evolutionCondition->getEvolutionTrigger()->getName();
            $label = $commonMarkConverter->convertToHtml($evolutionCondition->getLabel());
            $tree->addCondition($trigger, $label);
        }

        foreach ($pokemon->getEvolutionChildren() as $evolutionChild) {
            $tree->addChild($this->buildEvolutionTree($evolutionChild, $version, $commonMarkConverter));
        }

        return $tree;
    }

    static private function resolveVersion(?Version $version, Pokemon $pokemon): Version
    {
        if ($version) {
            return $version;
        }

        return $pokemon->getSpecies()->getVersionGroup()->getVersions()->first();
    }
}
