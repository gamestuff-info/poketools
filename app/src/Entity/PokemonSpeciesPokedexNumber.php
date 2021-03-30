<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The number of a species in a particular Pokédex
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonSpeciesPokedexNumberRepository")
 */
class PokemonSpeciesPokedexNumber implements EntityHasDefaultInterface
{

    use EntityHasDefaultTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonSpeciesInVersionGroup", inversedBy="numbers")
     * @ORM\Id()
     */
    private PokemonSpeciesInVersionGroup $species;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokedex")
     * @ORM\Id()
     * @Groups({"read"})
     */
    private Pokedex $pokedex;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private int $number;

    public function getSpecies(): ?PokemonSpeciesInVersionGroup
    {
        return $this->species;
    }

    public function setSpecies(PokemonSpeciesInVersionGroup $species): self
    {
        $this->species = $species;

        return $this;
    }

    public function getPokedex(): ?Pokedex
    {
        return $this->pokedex;
    }

    public function setPokedex(Pokedex $pokedex): self
    {
        $this->pokedex = $pokedex;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }
}
