<?php

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\PokemonForm;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonForm[]    findAll()
 * @method PokemonForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonFormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonForm::class);
    }

    /**
     * @param string $speciesSlug
     * @param string $pokemonSlug
     *
     * @return string[]
     */
    public function findAllSlugsForSpeciesPokemonSlug(string $speciesSlug, string $pokemonSlug): array
    {
        $qb = $this->createQueryBuilder('pokemon_form');
        $qb->select('pokemon_form.slug')
            ->distinct()
            ->join('pokemon_form.pokemon', 'pokemon')
            ->join('pokemon.species', 'species')
            ->where('species.slug = :speciesSlug')
            ->andWhere('pokemon.slug = :pokemonSlug')
            ->orderBy('pokemon_form.isDefault', 'DESC')
            ->setParameter('speciesSlug', $speciesSlug)
            ->setParameter('pokemonSlug', $pokemonSlug);
        $q = $qb->getQuery();
        $q->execute();

        return array_column($q->getArrayResult(), 'slug');
    }

    /**
     * @param string $speciesSlug
     * @param string $pokemonSlug
     * @param string $formSlug
     *
     * @return PokemonForm[]
     */
    public function findForAllVersions(string $speciesSlug, string $pokemonSlug, string $formSlug): array
    {
        $qb = $this->createQueryBuilder('form');
        $qb->join('form.pokemon', 'pokemon')
            ->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('form.slug = :formSlug')
            ->andWhere('pokemon.slug = :pokemonSlug')
            ->andWhere('species.slug = :speciesSlug')
            ->orderBy('version_group.position')
            ->setParameter('formSlug', $formSlug)
            ->setParameter('pokemonSlug', $pokemonSlug)
            ->setParameter('speciesSlug', $speciesSlug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param Pokemon $pokemon
     * @param Version $version
     * @param string|null $formSlug
     *   Pass null to find the default form for the pokemon.
     *
     * @return Pokemon|null
     */
    public function findOneByPokemon(Pokemon $pokemon, Version $version, ?string $formSlug): ?PokemonForm
    {
        $qb = $this->createQueryBuilder('form');
        $qb->join('form.pokemon', 'pokemon')
            ->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('form.pokemon = :pokemon')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('pokemon', $pokemon)
            ->setParameter('version', $version);

        if ($formSlug === null) {
            $qb->andWhere('form.isDefault = true');
        } else {
            $qb->andWhere('form.slug = :slug')
                ->setParameter('slug', $formSlug);
        }

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
