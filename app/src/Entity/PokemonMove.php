<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonMoveRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'learnMethod' => 'exact',
    'learnMethod.slug' => 'exact',
    'machine' => 'exact',
    'machine.slug' => 'exact',
    'move' => 'exact',
    'pokemon' => 'exact',
    'pokemon.species.versionGroup' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'position',
    'pokemon.species.position',
    'pokemon.position',
    'pokemon.name',
    'pokemon.types.type.position',
    'pokemon.abilities.ability.name',
    'pokemon.hp.baseValue',
    'pokemon.attack.baseValue',
    'pokemon.defense.baseValue',
    'pokemon.specialAttack.baseValue',
    'pokemon.specialDefense.baseValue',
    'pokemon.special.baseValue',
    'pokemon.speed.baseValue',
    'pokemon.statTotal',
    'move.name',
    'learnMethod.position',
    'level',
])]
class PokemonMove extends AbstractDexEntity implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon")
     * @Groups({"move_view"})
     */
    private Pokemon $pokemon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveInVersionGroup")
     * @Groups({"pokemon_view"})
     */
    private MoveInVersionGroup $move;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveLearnMethod")
     * @Groups({"read"})
     */
    private MoveLearnMethod $learnMethod;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="100")
     * @Groups({"read"})
     */
    private ?int $level;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup")
     * @Groups({"read"})
     */
    private ?ItemInVersionGroup $machine = null;

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getMove(): ?MoveInVersionGroup
    {
        return $this->move;
    }

    public function setMove(MoveInVersionGroup $move): self
    {
        $this->move = $move;

        return $this;
    }

    public function getLearnMethod(): ?MoveLearnMethod
    {
        return $this->learnMethod;
    }

    public function setLearnMethod(MoveLearnMethod $learnMethod): self
    {
        $this->learnMethod = $learnMethod;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getMachine(): ?ItemInVersionGroup
    {
        return $this->machine;
    }

    public function setMachine(?ItemInVersionGroup $machine): self
    {
        $this->machine = $machine;

        return $this;
    }
}
