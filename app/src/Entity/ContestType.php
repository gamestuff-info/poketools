<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Contest type, such as "cool" or "smart".
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContestTypeRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
    paginationClientEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'slug' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
class ContestType extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * The corresponding Berry flavor
     *
     * @ORM\OneToOne(targetEntity="App\Entity\BerryFlavor", inversedBy="contestType")
     */
    private BerryFlavor $berryFlavor;

    /**
     * The corresponding Pokéblock color
     *
     * @ORM\OneToOne(targetEntity="App\Entity\PokeblockColor")
     */
    private PokeblockColor $pokeblockColor;

    public function getBerryFlavor(): ?BerryFlavor
    {
        return $this->berryFlavor;
    }

    public function setBerryFlavor(BerryFlavor $berryFlavor): self
    {
        $this->berryFlavor = $berryFlavor;

        return $this;
    }

    public function getPokeblockColor(): ?PokeblockColor
    {
        return $this->pokeblockColor;
    }

    public function setPokeblockColor(PokeblockColor $pokeblockColor): self
    {
        $this->pokeblockColor = $pokeblockColor;

        return $this;
    }
}
