<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use App\Entity\Type;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pokémon knows a move of this type
 *
 * @ORM\Entity()
 */
class KnowsMoveTypeEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @Assert\NotBlank()
     */
    private Type $knowsMoveType;

    public function getLabel(): string
    {
        return sprintf('Knows a []{type:%s}-type move', $this->getKnowsMoveType()->getSlug());
    }

    public function getKnowsMoveType(): ?Type
    {
        return $this->knowsMoveType;
    }

    public function setKnowsMoveType(Type $knowsMoveType): self
    {
        $this->knowsMoveType = $knowsMoveType;

        return $this;
    }
}
