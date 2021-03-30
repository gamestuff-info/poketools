<?php

namespace App\Repository;

use App\Entity\MoveInVersionGroup;
use App\Entity\MoveLearnMethod;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MoveLearnMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveLearnMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveLearnMethod[]    findAll()
 * @method MoveLearnMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveLearnMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoveLearnMethod::class);
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return MoveLearnMethod[]
     */
    public function findUsedMethodsForPokemon(Pokemon $pokemon): array
    {
        $qb = $this->createQueryBuilder('learn_method');
        $qb->join(PokemonMove::class, 'pokemon_move', 'WITH', 'pokemon_move.learnMethod = learn_method')
            ->andWhere('pokemon_move.pokemon = :pokemon')
            ->setParameter('pokemon', $pokemon);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param MoveInVersionGroup|int $move
     *
     * @return QueryBuilder
     */
    public function findUsedMethodsForMoveQb(MoveInVersionGroup|int $move): QueryBuilder
    {
        $qb = $this->createQueryBuilder('move_learn_method');
        $qb->distinct()
            ->join(PokemonMove::class, 'pokemon_move', 'WITH', 'move_learn_method = pokemon_move.learnMethod')
            ->andWhere('pokemon_move.move = :move')
            ->setParameter('move', $move);

        return $qb;
    }

    /**
     * @param Pokemon|int $pokemon
     *
     * @return QueryBuilder
     */
    public function findUsedMethodsForPokemonQb(Pokemon|int $pokemon): QueryBuilder
    {
        $qb = $this->createQueryBuilder('move_learn_method');
        $qb->distinct()
            ->join(PokemonMove::class, 'pokemon_move', 'WITH', 'move_learn_method = pokemon_move.learnMethod')
            ->andWhere('pokemon_move.pokemon = :pokemon')
            ->setParameter('pokemon', $pokemon);

        return $qb;
    }
}
