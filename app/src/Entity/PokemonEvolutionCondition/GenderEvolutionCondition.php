<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\Gender;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * The Pokémon must be this gender.
 *
 * @ORM\Entity()
 */
class GenderEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gender")
     */
    private Gender $gender;

    public function getLabel(): string
    {
        return sprintf('Pokémon is %s', $this->getGender()->getName());
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }
}
