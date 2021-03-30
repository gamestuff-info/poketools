<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data for the Pal Park mini-game in Generation IV.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonPalParkDataRepository")
 */
class PokemonPalParkData
{

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pokemon", inversedBy="palParkData")
     * @ORM\Id()
     */
    private Pokemon $pokemon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PalParkArea", fetch="EAGER")
     * @ORM\Id()
     * @Groups({"read"})
     */
    private PalParkArea $area;

    /**
     * Used in calculating the player’s score at the end of a Pal Park run
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="100")
     * @Groups({"read"})
     */
    private int $score;

    /**
     * Base rate for encountering this Pokémon
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="100")
     * @Groups({"read"})
     */
    private int $rate;

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getArea(): ?PalParkArea
    {
        return $this->area;
    }

    public function setArea(PalParkArea $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }
}
