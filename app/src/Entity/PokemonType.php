<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonTypeRepository")
 */
class PokemonType implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="types")
     * @ORM\Id()
     */
    private Pokemon $pokemon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Type", fetch="EAGER")
     * @ORM\Id()
     * @Groups({"read"})
     */
    private Type $type;

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getType()->getName() ?? '';
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(Type $type): self
    {
        $this->type = $type;

        return $this;
    }
}
