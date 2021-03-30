<?php


namespace App\Helpers;

use App\Entity\AbilityInVersionGroup;
use App\Entity\AbstractDexEntity;
use App\Entity\EntityGroupedByGenerationInterface;
use App\Entity\EntityGroupedByVersionGroupInterface;
use App\Entity\EntityGroupedByVersionInterface;
use App\Entity\EntityHasSlugInterface;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationInVersionGroup;
use App\Entity\MoveInVersionGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\Type;
use App\Entity\Version;
use App\Repository\SlugAndVersionInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Resolve internal links to routes
 *
 * These links most often come from Markdown text, but can come from other
 * sources as well.
 */
class InternalLinkResolver
{
    // TODO: Enum
    public const TARGET_ABILITY = 'ability';
    public const TARGET_ITEM = 'item';
    public const TARGET_LOCATION = 'location';
    public const TARGET_MECHANIC = 'mechanic';
    public const TARGET_MOVE = 'move';
    public const TARGET_NATURE = 'nature';
    public const TARGET_POKEMON = 'pokemon';
    public const TARGET_TYPE = 'type';

    /**
     * These targets cannot be resolved without a version present.
     */
    private const TARGETS_NEED_VERSION = [
        self::TARGET_ABILITY,
        self::TARGET_ITEM,
        self::TARGET_LOCATION,
        self::TARGET_MOVE,
        self::TARGET_NATURE,
        self::TARGET_POKEMON,
        self::TARGET_TYPE,
    ];

    /**
     * Used to find the entity repository
     */
    private const TARGET_ENTITY_CLASSES = [
        self::TARGET_ABILITY => AbilityInVersionGroup::class,
        self::TARGET_ITEM => ItemInVersionGroup::class,
        self::TARGET_LOCATION => LocationInVersionGroup::class,
        self::TARGET_MOVE => MoveInVersionGroup::class,
        self::TARGET_NATURE => Nature::class,
        self::TARGET_POKEMON => PokemonSpeciesInVersionGroup::class,
        self::TARGET_TYPE => Type::class,
    ];

    /**
     * Used to turn entities into routes
     */
    private const CLASS_TARGETS = [
        AbilityInVersionGroup::class => self::TARGET_ABILITY,
        ItemInVersionGroup::class => self::TARGET_ITEM,
        LocationInVersionGroup::class => self::TARGET_LOCATION,
        MoveInVersionGroup::class => self::TARGET_MOVE,
        Nature::class => self::TARGET_NATURE,
        PokemonSpeciesInVersionGroup::class => self::TARGET_POKEMON,
        Pokemon::class => self::TARGET_POKEMON,
        Type::class => self::TARGET_TYPE,
    ];

    // TODO: Enum
    // These must be kept in sync with web/routes.ts
    private const TARGET_PATHS = [
        self::TARGET_ABILITY => '/dex/:version/ability/:slug',
        self::TARGET_ITEM => '/dex/:version/item/:slug',
        self::TARGET_LOCATION => '/dex/:version/location/:slug',
        self::TARGET_MOVE => '/dex/:version/move/:slug',
        self::TARGET_NATURE => '/dex/:version/nature/:slug',
        self::TARGET_POKEMON => '/dex/:version/pokemon/:species/:pokemon',
        self::TARGET_TYPE => '/dex/:version/type/:slug',
    ];

    private EntityManagerInterface $em;

    /**
     * InternalLinkResolver constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Get the location for a given internal link
     *
     * @param string $target One of the TARGET_* constants
     * @param string $slug The entity slug
     * @param Version|null $version The active version, or null if no version is active.
     *
     * @return string|null The location, or null if the link could not be resolved.
     */
    public function getLocation(string $target, string $slug, ?Version $version): ?string
    {
        if (!$version && in_array($target, self::TARGETS_NEED_VERSION)) {
            return null;
        }

        return match ($target) {
            self::TARGET_ABILITY,
            self::TARGET_ITEM,
            self::TARGET_LOCATION,
            self::TARGET_MOVE,
            self::TARGET_NATURE,
            self::TARGET_TYPE => $this->getGenericLocation($target, $slug, $version),
            self::TARGET_POKEMON => $this->getPokemonLocation($slug, $version),
            self::TARGET_MECHANIC => $this->getMechanicLocation($slug),
            default => throw new \ValueError('Invalid target: '.$target),
        };
    }

    private function getGenericLocation(string $target, string $slug, Version $version): string
    {
        return str_replace([':version', ':slug'], [(string)$version->getSlug(), $slug], self::TARGET_PATHS[$target]);
    }

    private function getPokemonLocation(string $slug, Version $version): string
    {
        $params = [':version' => (string)$version->getSlug()];
        [$params[':species'], $params[':pokemon']] = $this->parsePokemonSlug($slug);

        return str_replace(array_keys($params), array_values($params), self::TARGET_PATHS[self::TARGET_POKEMON]);
    }

    private function getMechanicLocation(string $slug): string
    {
        return sprintf(
            'https://bulbapedia.bulbagarden.net/wiki/Special:Search/%s',
            rawurlencode($slug)
        );
    }

    /**
     * Get the route and params for the internal link.
     *
     * @param string $target One of the TARGET_* constants
     * @param string $slug The entity slug
     * @param Version|null $version The active version, or null if no version is active.
     *
     * @return array A 2-tuple with the route name and an array of params.  If the link is valid but cannot be
     *     resolved, both elements will be null.
     *
     * @throws \ValueError When the target is not valid.
     * @deprecated
     */
    public function getRouteAndParams(string $target, string $slug, ?Version $version): array
    {
        if (!$version && in_array($target, self::TARGETS_NEED_VERSION)) {
            return [null, null];
        }
        $indexRoute = empty($slug);

        return match ($target) {
            self::TARGET_ABILITY,
            self::TARGET_ITEM,
            self::TARGET_LOCATION,
            self::TARGET_MOVE,
            self::TARGET_NATURE,
            self::TARGET_TYPE => $this->getGenericRouteAndParams($target, $slug, $version, $indexRoute),
            self::TARGET_MECHANIC => $this->getMechanicRouteAndParams($slug, $indexRoute),
            self::TARGET_POKEMON => $this->getPokemonRouteAndParams($slug, $version, $indexRoute),
            default => throw new \ValueError('Invalid target: '.$target),
        };
    }

    private function getMechanicRouteAndParams(string $slug, bool $indexRoute): array
    {
        return $indexRoute ? [null, null] : ['mechanic_view', ['mechanicSlug' => $slug]];
    }

    private function getGenericRouteAndParams(string $target, string $slug, Version $version, bool $indexRoute): array
    {
        $route = $indexRoute ? sprintf('%s_index', $target) : sprintf('%s_view', $target);
        $params = ['versionSlug' => $version->getSlug()];
        if (!$indexRoute) {
            $params[$target.'Slug'] = $slug;
        }

        return [$route, $params];
    }

    private function getPokemonRouteAndParams(string $slug, Version $version, bool $indexRoute): array
    {
        $route = $indexRoute ? 'pokemon_index' : 'pokemon_view';
        $params = ['versionSlug' => $version->getSlug()];
        if (!$indexRoute) {
            [$params['speciesSlug'], $params['pokemonSlug']] = $this->parsePokemonSlug($slug);
        }

        return [$route, $params];
    }

    /**
     * Parse a Pokemon slug
     *
     * This can be either a species only or also specify a Pokemon with `species/pokemon`.
     *
     * For example:
     * - `'bulbasaur'` => `['bulbasaur', null]`
     * - `'deoxys/deoxys-speed'` => `['deoxys', 'deoxys-speed']`
     *
     * @param string $slug
     *
     * @return array A 2-tuple with the species slug and Pokemon slug (or null if no Pokemon is specified)
     */
    private function parsePokemonSlug(string $slug): array
    {
        $slugParts = explode('/', $slug, 2);

        return [$slugParts[0], $slugParts[1] ?? null];
    }

    /**
     * Find the entity for the internal link
     *
     * @param string $target One of the TARGET_* constants
     * @param string $slug The entity slug
     * @param Version|null $version The active version, or null if no version is active.
     *
     * @return AbstractDexEntity|null The entity, or null if it does not exist. Null is also returned if a version is
     *     required but $version is null.
     *
     * @throws \ValueError When the target is not valid or does not correspond to an entity.
     */
    public function getEntityForLink(string $target, string $slug, ?Version $version): ?AbstractDexEntity
    {
        if (!$version && in_array($target, self::TARGETS_NEED_VERSION)) {
            return null;
        }

        return match ($target) {
            self::TARGET_ABILITY,
            self::TARGET_ITEM,
            self::TARGET_LOCATION,
            self::TARGET_MOVE,
            self::TARGET_NATURE,
            self::TARGET_TYPE => $this->getGenericEntityForLink($target, $slug, $version),
            self::TARGET_POKEMON => $this->getPokemonEntityForLink($slug, $version),
            default => throw new \ValueError('Target does not correspond to an entity: '.$target),
        };
    }

    private function getGenericEntityForLink(string $target, string $slug, Version $version): ?AbstractDexEntity
    {
        $repo = $this->em->getRepository(self::TARGET_ENTITY_CLASSES[$target]);

        if (is_subclass_of($repo, SlugAndVersionInterface::class)) {
            return $repo->findOneByVersion($slug, $version);
        } else {
            return $repo->findOneBy(['slug' => $slug]);
        }
    }

    private function getPokemonEntityForLink(string $slug, Version $version): PokemonSpeciesInVersionGroup|Pokemon|null
    {
        $speciesRepo = $this->em->getRepository(PokemonSpeciesInVersionGroup::class);
        [$speciesSlug, $pokemonSlug] = $this->parsePokemonSlug($slug);
        $species = $speciesRepo->findOneByVersion($speciesSlug, $version);
        if (!$species) {
            return null;
        } elseif (!$pokemonSlug) {
            // No Pokemon specified, only interested in the Species
            return $species;
        }

        $pokemonRepo = $this->em->getRepository(Pokemon::class);

        return $pokemonRepo->findOneBySpecies($species, $version, $pokemonSlug);
    }

    /**
     * Get the route and params for an entity.
     *
     * @param EntityHasSlugInterface $entity The entity to get a route for.
     * @param Version|null $fallbackVersion The active version, or null if no version is active.
     *
     * @return array A 2-tuple with the route name and an array of params.  If the link is valid but cannot be
     *     resolved, both elements will be null.
     *
     * @throws \ValueError When the target is not valid.
     * @deprecated
     */
    public function getEntityRouteAndParams(EntityHasSlugInterface $entity, ?Version $fallbackVersion): array
    {
        if (!isset(self::CLASS_TARGETS[get_class($entity)])) {
            throw new \ValueError('Entity is not linkable: '.get_class($entity));
        }
        $target = self::CLASS_TARGETS[get_class($entity)];

        $version = $this->resolveVersion($entity, $fallbackVersion);

        return match ($target) {
            self::TARGET_ABILITY,
            self::TARGET_ITEM,
            self::TARGET_LOCATION,
            self::TARGET_MOVE,
            self::TARGET_NATURE,
            self::TARGET_TYPE => $this->getRouteAndParams($target, $entity->getSlug(), $version),
            self::TARGET_POKEMON => $this->getPokemonEntityRouteAndParams($entity, $version),
        };
    }

    private function getPokemonEntityRouteAndParams(
        PokemonSpeciesInVersionGroup|Pokemon $entity,
        ?Version $version
    ): array {
        if ($entity instanceof PokemonSpeciesInVersionGroup) {
            return $this->getRouteAndParams(self::TARGET_POKEMON, $entity->getSlug(), $version);
        } elseif ($entity instanceof Pokemon) {
            return $this->getRouteAndParams(
                self::TARGET_POKEMON,
                sprintf('%s/%s', $entity->getSpecies()->getSlug(), $entity->getSlug()),
                $version
            );
        }
        throw new \LogicException('Unhandled Pokemon entity class: '.get_class($entity));
    }

    /**
     * Choose a version to use for generated route
     *
     * Will always use $version if it falls within the entity's grouping, otherwise will use the first version from the
     * entity's group.
     *
     * If the entity has no group, will use $version.
     *
     * @param object $entity
     * @param Version|null $version
     *
     * @return Version|null
     */
    private function resolveVersion(object $entity, ?Version $version): ?Version
    {
        $useVersion = $version;

        // Pokemon's grouping info is stored one level up, in the species
        if ($entity instanceof Pokemon) {
            $entity = $entity->getSpecies();
        }

        if (is_subclass_of($entity, EntityGroupedByVersionInterface::class)) {
            // Entity already declares a version; use that.
            $useVersion = $entity->getVersion();
        } elseif (is_subclass_of($entity, EntityGroupedByVersionGroupInterface::class)) {
            // Entity only declares a version group
            $versionGroup = $entity->getVersionGroup();
            if ($version) {
                if ($versionGroup === $version->getVersionGroup()) {
                    // Requested version is a member of the version group; use that.
                    $useVersion = $version;
                } else {
                    // Entity declares a different version group; use the first version in the group.
                    $useVersion = $versionGroup->getVersions()->first();
                }
            } else {
                // No version requested; use the first version in the entity's version group.
                $useVersion = $versionGroup->getVersions()->first();
            }
        } elseif (is_subclass_of($entity, EntityGroupedByGenerationInterface::class)) {
            // Entity only declares a generation
            $generation = $entity->getGeneration();
            if ($version) {
                if ($generation === $version->getVersionGroup()->getGeneration()) {
                    // Requested version is a member of the generation; use that.
                    $useVersion = $version;
                } else {
                    // Entity declares a different generation; use the first version in the generation.
                    $useVersion = $generation->getVersionGroups()->first()->getVersions()->first();
                }
            } else {
                // No version requested; use the first version in the generation.
                $useVersion = $generation->getVersionGroups()->first()->getVersions()->first();
            }
        }

        return $useVersion;
    }
}
