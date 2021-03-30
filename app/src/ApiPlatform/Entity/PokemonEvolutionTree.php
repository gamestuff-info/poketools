<?php


namespace App\ApiPlatform\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Pokemon;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    paginationEnabled: false,
    collectionOperations: [],
)]
class PokemonEvolutionTree
{
    /**
     * @Groups({"read"})
     */
    #[ApiProperty(identifier: true)]
    private Pokemon $pokemon;

    /**
     * @Groups({"read"})
     */
    private array $conditions = [];

    /**
     * @var array<PokemonEvolutionTree>
     * @Groups({"read"})
     */
    private array $children = [];

    /**
     * @return Pokemon
     */
    public function getPokemon(): Pokemon
    {
        return $this->pokemon;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return PokemonEvolutionTree
     */
    public function setPokemon(Pokemon $pokemon): PokemonEvolutionTree
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param string $trigger
     * @param string $condition
     *
     * @return PokemonEvolutionTree
     */
    public function addCondition(string $trigger, string $condition): PokemonEvolutionTree
    {
        $this->conditions[$trigger][] = $condition;

        return $this;
    }

    /**
     * @return array<PokemonEvolutionTree>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(PokemonEvolutionTree $child): PokemonEvolutionTree
    {
        $this->children[] = $child;

        return $this;
    }
}
