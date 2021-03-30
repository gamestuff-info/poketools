<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\MoveInVersionGroup;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Pokémon knows this move.
 *
 * @ORM\Entity()
 */
class KnowsMoveEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveInVersionGroup")
     * @Assert\NotBlank()
     */
    private MoveInVersionGroup $knowsMove;

    public function getLabel(): string
    {
        return sprintf('Knows []{move:%s}', $this->getKnowsMove()->getSlug());
    }

    public function getKnowsMove(): ?MoveInVersionGroup
    {
        return $this->knowsMove;
    }

    public function setKnowsMove(MoveInVersionGroup $knowsMove): self
    {
        $this->knowsMove = $knowsMove;

        return $this;
    }
}
