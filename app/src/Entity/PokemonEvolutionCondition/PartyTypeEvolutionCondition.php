<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use App\Entity\Type;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Pokémon of this type must be present in the party.
 *
 * @ORM\Entity()
 */
class PartyTypeEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @Assert\NotBlank()
     */
    private Type $partyType;

    public function getLabel(): string
    {
        return sprintf('[]{type:%s}-type Pokémon is in the current party', $this->getPartyType()->getSlug());
    }

    public function getPartyType(): ?Type
    {
        return $this->partyType;
    }

    public function setPartyType(Type $partyType): self
    {
        $this->partyType = $partyType;

        return $this;
    }
}
