<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonAbilityRepository")
 */
class PokemonAbility implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="abilities")
     * @ORM\Id()
     */
    private Pokemon $pokemon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AbilityInVersionGroup")
     * @ORM\Id()
     * @Groups({"read"})
     */
    private AbilityInVersionGroup $ability;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private bool $hidden = false;

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getAbility()->getName() ?? 'None';
    }

    public function getAbility(): ?AbilityInVersionGroup
    {
        return $this->ability;
    }

    public function setAbility(AbilityInVersionGroup $ability): self
    {
        $this->ability = $ability;

        return $this;
    }
}
