<?php


namespace App\Search;


use App\Entity\AbilityInVersionGroup;
use App\Entity\AbstractDexEntity;
use App\Entity\EntityGroupedByGenerationInterface;
use App\Entity\EntityGroupedByVersionGroupInterface;
use App\Entity\EntityGroupedByVersionInterface;
use App\Entity\EntityHasNameInterface;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationInVersionGroup;
use App\Entity\MoveInVersionGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\Type;
use App\Entity\Version;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ds\Map;
use Ds\Set;
use TeamTNT\TNTSearch\TNTSearch;

/**
 * Find entities
 */
class Finder
{
    private const TNT_AS_YOU_TYPE = 'asYouType';
    private const TNT_MAX_DOCS = 'maxDocs';
    private const TNT_FUZZINESS = 'fuzziness';
    private const TNT_FUZZY_PREFIX_LENGTH = 'fuzzy_prefix_length';
    private const TNT_FUZZY_DISTANCE = 'fuzzy_distance';
    private const TNT_STATE_PROPS = [
        self::TNT_AS_YOU_TYPE,
        self::TNT_MAX_DOCS,
        self::TNT_FUZZINESS,
        self::TNT_FUZZY_PREFIX_LENGTH,
        self::TNT_FUZZY_DISTANCE,
    ];

    private TNTSearch $tnt;
    private EntityManagerInterface $em;

    /**
     * Finder constructor.
     *
     * @param TNTSearch $tnt
     * @param EntityManagerInterface $em
     */
    public function __construct(TNTSearch $tnt, EntityManagerInterface $em)
    {
        $this->tnt = $tnt;
        $this->em = $em;
    }

    /**
     * Find autocomplete candidates
     *
     * @param string $term
     * @param Version|null $version
     *
     * @return EntityHasNameInterface[]|AbstractDexEntity[]
     */
    public function autocomplete(string $term, ?Version $version): array
    {
        $classes = [
            'pokemon' => Pokemon::class,
            'move' => MoveInVersionGroup::class,
            'type' => Type::class,
            'item' => ItemInVersionGroup::class,
            'location' => LocationInVersionGroup::class,
            'nature' => Nature::class,
            'ability' => AbilityInVersionGroup::class,
        ];
        // This is the same format results come in from TNTSearch, but gives more control
        // since we just want results where the term appears in the beginning.
        $q = $this->em->getConnection()->executeQuery(
            <<<SQL
            SELECT "id"
            FROM (
                     SELECT '${classes['pokemon']}__' || "pokemon"."id" AS "id", "pokemon"."name"
                     FROM "pokemon"
                              JOIN "pokemon_species_in_version_group" "species" ON "pokemon"."species_id" = "species"."id"
                     WHERE (:versionGroup IS NULL OR "version_group_id" = :versionGroup)
                       AND "pokemon"."name" LIKE (:term || '%')
                     UNION
                     SELECT '${classes['move']}__' || "id" AS "id", "name"
                     FROM "move_in_version_group"
                     WHERE "version_group_id" = :versionGroup
                       AND "name" LIKE (:term || '%')
                     UNION
                     SELECT DISTINCT '${classes['type']}__' || "type"."id" AS "id", "type"."name"
                     FROM "type_efficacy"
                              JOIN "type" ON "type_efficacy"."attacking_type_id" = "type"."id"
                              JOIN "type_chart_version_group"
                                   ON "type_efficacy"."type_chart_id" = "type_chart_version_group"."type_chart_id"
                     WHERE (:versionGroup IS NULL OR "type_chart_version_group"."version_group_id" = :versionGroup)
                       AND "type"."name" LIKE (:term || '%')
                     UNION
                     SELECT '${classes['item']}__' || "id" AS "id", "name"
                     FROM "item_in_version_group"
                     WHERE (:versionGroup IS NULL OR "version_group_id" = :versionGroup)
                       AND "name" LIKE (:term || '%')
                     UNION
                     SELECT '${classes['ability']}__' || "id" AS "id", "name"
                     FROM "location_in_version_group"
                     WHERE (:versionGroup IS NULL OR "version_group_id" = :versionGroup)
                       AND "name" LIKE (:term || '%')
                     UNION
                     SELECT '${classes['nature']}__' || "id" AS "id", "name"
                     FROM "nature"
                     WHERE (:versionGroup IS NULL OR (
                               -- Only show natures when the version group supports natures
                               SELECT COUNT()
                               FROM "version_group_feature"
                               WHERE "version_group_id" = :versionGroup
                                 AND "feature_id" = (SELECT "id" FROM "feature" WHERE "slug" = 'natures')
                           ) > 0)
                       AND "name" LIKE (:term || '%')
                     UNION
                     SELECT '${classes['ability']}__' || "id" AS "id", "name"
                     FROM "ability_in_version_group"
                     WHERE (:versionGroup IS NULL OR "version_group_id" = :versionGroup)
                       AND "name" LIKE (:term || '%')
                 )
            ORDER BY "name"
            LIMIT 10;
        SQL,
            ['term' => $term, 'versionGroup' => $version?->getVersionGroup()->getId()]
        );
        $ids = $q->fetchFirstColumn();

        return $this->hydrateResults($ids, $version);
    }

    /**
     * Search for entities
     *
     * @param string $term
     * @param Version|null $version
     *
     * @return AbstractDexEntity[]
     */
    public function search(string $term, ?Version $version): array
    {
        return $this->doSearch(
            Indexer::IX_NAME,
            $term,
            $version,
            [
                self::TNT_FUZZINESS => true,
            ]
        );
    }

    /**
     * Perform the query
     *
     * @param string $term
     * @param Version|null $version
     * @param array $options
     *  An map of TNT_* constants to values
     *
     * @return AbstractDexEntity[]
     */
    private function doSearch(string $index, string $term, ?Version $version, array $options = []): array
    {
        $oldState = $this->saveTntState();
        foreach ($options as $option => $value) {
            $this->tnt->$option = $value;
        }

        $this->tnt->selectIndex($index);
        $searchResults = $this->tnt->search($term);
        $this->restoreTntState($oldState);
        if ($searchResults['hits'] === 0) {
            return [];
        }

        $entities = $this->hydrateResults($searchResults['ids'], $version);

        return $entities;
    }

    /**
     * Hydrate results from database
     *
     * @param array $ids
     * @param Version|null $version
     *
     * @return AbstractDexEntity[]
     */
    private function hydrateResults(array $ids, ?Version $version): array
    {
        if (empty($ids)) {
            return [];
        }

        // Group results by class to reduce queries
        $classEntityIdMap = new Map();
        foreach ($ids as &$classId) {
            $classId = explode('__', $classId);
            [$class, $id] = $classId;
            if (!$classEntityIdMap->hasKey($class)) {
                $classEntityIdMap->put($class, new Set());
            }
            /** @var Set $classEntityIds */
            $classEntityIds = $classEntityIdMap->get($class);
            $classEntityIds->add($id);
        }
        unset($classId);
        $classEntityMap = new Map();
        /** @var Map $idEntities */
        foreach ($classEntityIdMap as $class => $classIds) {
            $hydrated = match ($class) {
                Pokemon::class => $this->findPokemon($classIds->toArray(), $version),
                default => $this->findEntities($class, $classIds->toArray(), $version),
            };
            $classEntityMap->put($class, new Map());
            /** @var AbstractDexEntity $entity */
            foreach ($hydrated as $entity) {
                $classEntityMap[$class][$entity->getId()] = $entity;
            }
        }
        unset($idEntities);

        $entities = [];
        // $ids contains the order entities should be returned in.
        foreach ($ids as $classId) {
            [$class, $id] = $classId;
            if (!$classEntityMap[$class]->hasKey((int)$id)) {
                continue;
            }
            $entities[] = $classEntityMap[$class][(int)$id];
        }

        return $entities;
    }

    /**
     * Find entities from the repository
     *
     * @param string $class
     * @param array $ids
     * @param Version|null $version
     *
     * @return array
     */
    private function findEntities(string $class, array $ids, ?Version $version): array
    {
        $repo = $this->em->getRepository($class);
        $q = ['id' => $ids];
        if ($version) {
            if (is_subclass_of($class, EntityGroupedByVersionInterface::class)) {
                $q[$class::getGroupField()] = $version;
            } elseif (is_subclass_of($class, EntityGroupedByVersionGroupInterface::class)) {
                $q[$class::getGroupField()] = $version->getVersionGroup();
            } elseif (is_subclass_of($class, EntityGroupedByGenerationInterface::class)) {
                $q[$class::getGroupField()] = $version->getVersionGroup()->getGeneration();
            }
        }

        return $repo->findBy($q);
    }

    /**
     * Find Pokemon
     *
     * This is a special case as Pokemon's grouping information is stored one level up,
     * in the species.
     *
     * @param array $ids
     * @param Version|null $version
     *
     * @return array
     */
    private function findPokemon(array $ids, ?Version $version): array
    {
        /** @var PokemonRepository $repo */
        $repo = $this->em->getRepository(Pokemon::class);

        if (!$version) {
            // No grouping
            return $repo->findBy(['id' => $ids]);
        }

        $qb = $repo->createQueryBuilder('pokemon');
        $qb->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('pokemon.id IN (:ids)')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('ids', $ids)
            ->setParameter('version', $version);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * Save current TNT state
     *
     * @return Map
     */
    private function saveTntState(): Map
    {
        $state = new Map();
        foreach (self::TNT_STATE_PROPS as $prop) {
            $state[$prop] = $this->tnt->$prop;
        }

        return $state;
    }

    /**
     * Restore TNT state
     *
     * @param Map $state
     */
    private function restoreTntState(Map $state): void
    {
        foreach (self::TNT_STATE_PROPS as $prop) {
            $this->tnt->$prop = $state[$prop];
        }
    }
}
