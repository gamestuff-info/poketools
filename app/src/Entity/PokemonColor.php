<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\CssColor as AssertCssColor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The "Pokédex color" of a Pokémon species. Usually based on the Pokémon’s
 * primary color.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonColorRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC']
)]
class PokemonColor extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * The CSS color string that represents this color
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @AssertCssColor()
     * @Groups({"read"})
     */
    private string $cssColor;

    public function getCssColor(): ?string
    {
        return $this->cssColor;
    }

    public function setCssColor(string $cssColor): self
    {
        $this->cssColor = $cssColor;

        return $this;
    }
}
