<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonFlavorTextRepository")
 */
class PokemonFlavorText implements EntityHasFlavorTextInterface, EntityIsSortableInterface
{

    use EntityHasFlavorTextTrait;
    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="flavorText")
     * @ORM\Id()
     */
    private Pokemon $pokemon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Version")
     * @ORM\Id()
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    private Version $version;

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(Version $version): self
    {
        $this->version = $version;
        $this->setPosition($version->getPosition());

        return $this;
    }

    public function __toString(): string
    {
        return $this->getFlavorText() ?? '';
    }
}
