<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Pokémon must be at least this level.
 *
 * @ORM\Entity()
 */
class MinimumLevelEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min="1", max="100")
     */
    private int $minimumLevel;

    public function getLabel(): string
    {
        return sprintf('Level is at least %u', $this->getMinimumLevel());
    }

    public function getMinimumLevel(): ?int
    {
        return $this->minimumLevel;
    }

    public function setMinimumLevel(int $minimumLevel): self
    {
        $this->minimumLevel = $minimumLevel;

        return $this;
    }
}
