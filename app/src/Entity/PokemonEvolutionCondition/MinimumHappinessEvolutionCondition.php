<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Pokémon's internal happiness value must be at least this.
 *
 * @ORM\Entity()
 */
class MinimumHappinessEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="1", max="255")
     */
    private int $minimumHappiness;

    public function getLabel(): string
    {
        return sprintf('Happiness is at least %u', $this->getMinimumHappiness());
    }

    public function getMinimumHappiness(): ?int
    {
        return $this->minimumHappiness;
    }

    public function setMinimumHappiness(int $minimumHappiness): self
    {
        $this->minimumHappiness = $minimumHappiness;

        return $this;
    }
}
