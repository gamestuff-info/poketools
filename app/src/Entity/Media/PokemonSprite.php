<?php
/**
 * @file PokemonSprite.php
 */

namespace App\Entity\Media;

use App\Entity\PokemonForm;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Pokemon Sprite
 *
 * @ORM\Entity()
 */
class PokemonSprite extends AbstractMediaEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonForm", inversedBy="sprites")
     * @ORM\Id()
     */
    private PokemonForm $pokemonForm;

    public function getPokemonForm(): ?PokemonForm
    {
        return $this->pokemonForm;
    }

    public function setPokemonForm(PokemonForm $pokemonForm): self
    {
        $this->pokemonForm = $pokemonForm;

        return $this;
    }
}
