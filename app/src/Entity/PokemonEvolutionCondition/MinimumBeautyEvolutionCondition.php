<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Pokémon's internal beauty value must be at least this.
 *
 * @ORM\Entity()
 */
class MinimumBeautyEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="1", max="255")
     */
    private int $minimumBeauty;

    public function getLabel(): string
    {
        return sprintf('Beauty is at least %u', $this->getMinimumBeauty());
    }

    public function getMinimumBeauty(): ?int
    {
        return $this->minimumBeauty;
    }

    public function setMinimumBeauty(int $minimumBeauty): self
    {
        $this->minimumBeauty = $minimumBeauty;

        return $this;
    }
}
