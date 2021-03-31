<?php

namespace App\Repository;

use App\Entity\ItemInVersionGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\PokemonEvolutionCondition\HeldItemEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\TriggerItemEvolutionCondition;
use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\Version;
use App\Entity\VersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use LogicException;

/**
 * @method Pokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pokemon[]    findAll()
 * @method Pokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonRepository extends MaterializedPathRepository implements ServiceEntityRepositoryInterface, SlugAndVersionInterface
{
    use PagingTrait;

    const POKEMON_STAT_DQL = [
        'hp' => 'hp.baseValue',
        'attack' => 'attack.baseValue',
        'defense' => 'defense.baseValue',
        'special-attack' => 'specialAttack.baseValue',
        'special-defense' => 'specialDefense.baseValue',
        'special' => 'special.baseValue',
        'speed' => 'speed.baseValue',
    ];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = Pokemon::class;
        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass($entityClass);

        if ($manager === null) {
            throw new LogicException(
                sprintf(
                    'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                    $entityClass
                )
            );
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }

    /**
     * @param string $speciesSlug
     * @param string $pokemonSlug
     *
     * @return Pokemon[]
     */
    public function findForAllVersions(string $speciesSlug, string $pokemonSlug): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('species.slug = :speciesSlug')
            ->andWhere('pokemon.slug = :pokemonSlug')
            ->orderBy('version_group.position')
            ->setParameter('speciesSlug', $speciesSlug)
            ->setParameter('pokemonSlug', $pokemonSlug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByVersion(string $slug, Version|string $version): ?Pokemon
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('pokemon.slug = :slug')
            ->setParameter('slug', $slug)
            ->setParameter('version', $version);
        if ($version instanceof Version) {
            $qb->andWhere(':version MEMBER OF version_group.versions');
        } else {
            $qb->join('version_group.versions', 'version')
                ->andWhere('version.slug = :version');
        }
        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }

    /**
     * @param string $speciesSlug
     *
     * @return string[]
     */
    public function findAllSlugsForSpeciesSlug(string $speciesSlug): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->select('pokemon.slug')
            ->distinct()
            ->join('pokemon.species', 'species')
            ->where('species.slug = :speciesSlug')
            ->orderBy('pokemon.isDefault', 'DESC')
            ->setParameter('speciesSlug', $speciesSlug);
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @return array{versionSlug: string, speciesSlug: string, pokemonSlug: string, name: string}
     */
    public function findForSearchIndex(): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->select('pokemon.slug pokemonSlug')
            ->addSelect('species.slug speciesSlug')
            ->addSelect('version.slug versionSlug')
            ->addSelect('pokemon.name name')
            ->addSelect('form.icon icon')
            ->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->join('version_group.versions', 'version')
            ->join('pokemon.forms', 'form', 'WITH', 'form.isDefault = 1');
        $q = $qb->getQuery();
        $q->execute();

        return $q->getArrayResult();
    }

    /**
     * @param PokemonSpeciesInVersionGroup $species
     * @param Version|string $version
     * @param string|null $pokemonSlug
     *   Pass null to find the default pokemon for the species.
     *
     * @return Pokemon|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySpecies(
        PokemonSpeciesInVersionGroup $species,
        Version|string $version,
        ?string $pokemonSlug
    ): ?Pokemon {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('pokemon.species = :species')
            ->setMaxResults(1)
            ->setParameter('species', $species)
            ->setParameter('version', $version);
        if ($version instanceof Version) {
            $qb->andWhere(':version MEMBER OF version_group.versions');
        } else {
            $qb->join('version_group.versions', 'version')
                ->andWhere('version.slug = :version');
        }
        if ($pokemonSlug === null) {
            $qb->andWhere('pokemon.isDefault = true');
        } else {
            $qb->andWhere('pokemon.slug = :slug')
                ->setParameter('slug', $pokemonSlug);
        }

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }

    /**
     * @param Version $version
     *
     * @return Pokemon[]
     */
    public function findAllByVersion(Version $version): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->andWhere('pokemon.mega = 0')
            ->addOrderBy('species.position')
            ->addOrderBy('pokemon.position')
            ->setParameter('version', $version);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * Build the evolution tree.
     *
     * This will create an array with the elements
     * "entity" => The Pokemon entity,
     * "active" => Is this Pokemon the active (source of the evolution tree request) Pokemon?
     * "children" => A list of evolution children.  Each item is the same format as this array.
     *
     * @param Pokemon $pokemon
     * @param Pokemon|null $activePokemon
     * @param bool $rootCall
     *
     * @psalm-type EvolutionTreeLeaf = array {entity: Pokemon, active: bool, children: EvolutionTreeLeaf[]}
     * @return EvolutionTreeLeaf
     */
    public function buildEvolutionTree(Pokemon $pokemon, ?Pokemon $activePokemon = null, bool $rootCall = true): array
    {
        if ($rootCall) {
            $activePokemon = $pokemon;
            // Only perform more queries if we have to
            $pokemon = $pokemon->getEvolutionParent() === null ? $pokemon : $this->findEvolutionRoot($pokemon);
        }

        $tree = [
            'entity' => $pokemon,
            'active' => $pokemon === $activePokemon,
            'children' => [],
        ];

        /** @var Pokemon[] $children */
        $children = $pokemon->getEvolutionChildren();
        foreach ($children as $child) {
            if ($child->isMega()) {
                continue;
            }
            $tree['children'][] = $this->buildEvolutionTree($child, $activePokemon, false);
        }

        return $tree;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return Pokemon
     */
    public function findEvolutionRoot(Pokemon $pokemon): Pokemon
    {
        $qb = $this->getPathQueryBuilder($pokemon);
        $qb->setMaxResults(1);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }

    /**
     * Find all Pokemon (and their evolution parents and children) that evolving using an item.
     *
     * @param Version $version
     * @param string $item Item slug
     *
     * @return QueryBuilder
     */
    public function findItemEvolutionFamiliesQb(Version $version, string $item): QueryBuilder
    {
        // Get the specific Pokemon that evolve with the item
        $triggerItemQb = new QueryBuilder($this->getEntityManager());
        $triggerItemQb->from(TriggerItemEvolutionCondition::class, 'trigger_item_evolution_condition')
            ->select('trigger_item_evolution_condition.id')
            ->join('trigger_item_evolution_condition.evolutionTrigger', 'trigger_item_evolution_trigger')
            ->join('trigger_item_evolution_condition.triggerItem', 'trigger_item')
            ->where("trigger_item_evolution_trigger.slug = 'use-item'")
            ->andWhere('trigger_item.slug = :item');
        $qb = $this->createQueryBuilder('pokemon');
        $qb->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->join('pokemon.evolutionConditions', 'evolution_conditions')
            ->andWhere($qb->expr()->in('evolution_conditions.id', $triggerItemQb->getDQL()))
            ->andWhere('pokemon.mega = false')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->orderBy('species.position')
            ->addOrderBy('pokemon.position')
            ->setParameter('item', $item)
            ->setParameter('version', $version);

        return $qb;
    }

    /**
     * Find Pokemon that are a good fit for a certain nature.
     *
     * "Neutral" natures are a good fit for Pokemon with highest and lowest stats at most 10 apart.
     *
     * Other natures are a good fit for Pokemon with:
     * - Highest and lowest stats are more than 10 apart.
     * - Highest stat is improved by this nature
     * - Lowest stat is hindered by this nature.
     */
    public function findPokemonForNatureQb(Nature $nature): QueryBuilder
    {
        $idealPokemonStatDiff = 10;

        $qb = $this->createQueryBuilder('pokemon');

        // VALUEMAX and VALUEMIN become SQL MAX and MIN, but have different names so as
        // not to conflict with the aggregate functions.
        $minStatFields = sprintf('VALUEMIN(%s)', implode(', ', self::statFieldsDQL('pokemon', 65535, skipHp: true)));
        $maxStatFields = sprintf('VALUEMAX(%s)', implode(', ', self::statFieldsDQL('pokemon', 0, skipHp: true)));
        $qb->andWhere(
            sprintf(
                '%s - %s %s %d',
                $maxStatFields,
                $minStatFields,
                // Stats difference is below or above 10 depending on if the nature is neutral or not.
                $nature->isNeutral() ? '<=' : '>',
                $idealPokemonStatDiff
            )
        );

        if (!$nature->isNeutral()) {
            // Best fit for:
            // - Highest and lowest stats are more than 10 apart.
            // - Highest stat is improved by this nature
            // - Lowest stat is hindered by this nature.
            $qb->andWhere(
                sprintf(
                    '%s = pokemon.%s',
                    $maxStatFields,
                    self::POKEMON_STAT_DQL[$nature->getStatIncreased()->getSlug()]
                )
            )->andWhere(
                sprintf(
                    '%s = pokemon.%s',
                    $minStatFields,
                    self::POKEMON_STAT_DQL[$nature->getStatDecreased()->getSlug()]
                )
            );
        }

        return $qb;
    }

    /**
     * Create a DQL snippet with coalesced pokemon stat fields
     *
     * @param string $table
     * @param int $default
     *  Default value.  Not sanitized!
     * @param bool $skipHp
     *
     * @return array
     */
    static private function statFieldsDQL(string $table, int $default, bool $skipHp = false): array
    {
        $dqlSnippets = array_map(
            fn(string $statField) => "COALESCE($table.$statField, $default)",
            self::POKEMON_STAT_DQL
        );
        if ($skipHp) {
            unset($dqlSnippets['hp']);
        }

        return $dqlSnippets;
    }

    public function countInVersionGroup(VersionGroup $versionGroup): int
    {
        $qb = $this->createQueryBuilder('pokemon')
            ->select('COUNT(pokemon.id)')
            ->join('pokemon.species', 'species')
            ->where('species.versionGroup = :versionGroup')
            ->setParameter('versionGroup', $versionGroup);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getSingleScalarResult();
    }

    public function calcStatPercentiles(Pokemon $pokemon): array
    {
        $lessCounts = $this->getStatValueCounts($pokemon, '<');
        $equalCounts = $this->getStatValueCounts($pokemon, '=');
        $count = $this->countInVersionGroup($pokemon->getSpecies()->getVersionGroup());

        $percentiles = [];
        foreach (array_keys($lessCounts) as $stat) {
            $less = $lessCounts[$stat];
            $equal = $equalCounts[$stat];
            $percentiles[$stat] = (($less + ($equal / 2.0)) / $count) * 100;
        }

        return $percentiles;
    }

    /**
     * Get the count of Pokemon with their stats less than the given Pokemon's.
     *
     * @param Pokemon $pokemon
     * @param '<'|'=' $comp
     *
     * @return array<string, int> An array keyed by stat slug, plus 'total'
     */
    private function getStatValueCounts(Pokemon $pokemon, string $comp): array
    {
        $statValueCounts = [];
        $qb = $this->createQueryBuilder('pokemon');
        $qb->select('COUNT(pokemon.id)')
            ->join('pokemon.species', 'species')
            ->setParameter('versionGroup', $pokemon->getSpecies()->getVersionGroup())
            ->setParameter('pokemon', $pokemon);

        // Stat counts
        foreach (self::POKEMON_STAT_DQL as $statSlug => $statDql) {
            $compQb = $this->createQueryBuilder('pokemon_stat');
            $compQb->select('pokemon_stat.'.$statDql)
                ->where('pokemon_stat = :pokemon');
            // Reset previous conditions
            $qb->where(
                sprintf(
                    'pokemon.%s %s (%s)',
                    $statDql,
                    $comp,
                    $compQb->getDQL()
                )
            )->andWhere('species.versionGroup = :versionGroup');
            $q = $qb->getQuery();
            $q->execute();
            $statValueCounts[$statSlug] = $q->getSingleScalarResult();
        }

        // Total count
        $compQb = $this->createQueryBuilder('pokemon_stat');
        $compQb->select(sprintf('(%s)', implode(' + ', self::statFieldsDQL('pokemon_stat', 0))))
            ->where('pokemon_stat = :pokemon');
        // Reset previous conditions
        $qb->where(
            sprintf(
                '(%s) %s (%s)',
                implode(' + ', self::statFieldsDQL('pokemon', 0)),
                $comp,
                $compQb->getDQL()
            )
        )->andWhere('species.versionGroup = :versionGroup');
        $q = $qb->getQuery();
        $q->execute();
        $statValueCounts['total'] = $q->getSingleScalarResult();

        return $statValueCounts;
    }

    /**
     * Find Pokemon that evolve with the given item
     *
     * @param ItemInVersionGroup|int $item
     *
     * @return QueryBuilder
     */
    public function evolvesWithItemSlugQb(string $itemSlug): QueryBuilder
    {
        $triggerItemQb = $this->getEntityManager()->createQueryBuilder();
        $triggerItemQb->from(TriggerItemEvolutionCondition::class, 'trigger_item_evolution_condition')
            ->select('trigger_item_evolution_condition.id')
            ->join('trigger_item_evolution_condition.evolutionTrigger', 'trigger_item_evolution_trigger')
            ->join('trigger_item_evolution_condition.triggerItem', 'trigger_item')
            ->where("trigger_item_evolution_trigger.slug = 'use-item'")
            ->andWhere('trigger_item.slug = :itemSlug');

        $heldItemQb = $this->getEntityManager()->createQueryBuilder();
        $heldItemQb->from(HeldItemEvolutionCondition::class, 'held_item_evolution_condition')
            ->select('held_item_evolution_condition.id')
            ->join('held_item_evolution_condition.evolutionTrigger', 'held_item_evolution_trigger')
            ->join('held_item_evolution_condition.heldItem', 'held_item')
            ->andWhere('held_item.slug = :itemSlug');

        $qb = $this->createQueryBuilder('pokemon');
        $qb->join('pokemon.evolutionConditions', 'evolution_conditions')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('evolution_conditions.id', $triggerItemQb->getDQL()),
                    $qb->expr()->in('evolution_conditions.id', $heldItemQb->getDQL())
                )
            )
            ->andWhere('pokemon.mega = false')
            ->setParameter('itemSlug', $itemSlug);

        return $qb;
    }
}
