<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The conditions under which a Pokémon will evolve into this Pokémon.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonEvolutionConditionRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
class PokemonEvolutionCondition extends AbstractDexEntity
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="evolutionConditions")
     * @Assert\NotBlank()
     */
    private Pokemon $pokemon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EvolutionTrigger")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private EvolutionTrigger $evolutionTrigger;

    public function __construct()
    {
        // Placeholder for overrides
    }

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getEvolutionTrigger(): EvolutionTrigger
    {
        return $this->evolutionTrigger;
    }

    public function setEvolutionTrigger(EvolutionTrigger $evolutionTrigger): self
    {
        $this->evolutionTrigger = $evolutionTrigger;

        return $this;
    }

    /**
     * Subclasses should override this to return something helpful.
     *
     * @return string
     * @Groups({"read"})
     */
    public function getLabel(): string
    {
        return '';
    }
}
