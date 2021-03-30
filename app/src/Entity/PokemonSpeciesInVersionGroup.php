<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Pokémon species is a single named entity in the Pokédex.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonSpeciesInVersionGroupRepository")
 *
 * @method PokemonSpecies getParent()
 * @method self setParent(PokemonSpecies $parent)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'slug' => 'exact',
    'versionGroup' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['name', 'position'])]
class PokemonSpeciesInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var PokemonSpecies
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonSpecies", inversedBy="children")
     */
    protected EntityHasChildrenInterface $parent;

    /**
     * @var Collection<PokemonSpeciesPokedexNumber>
     * @ORM\OneToMany(targetEntity="PokemonSpeciesPokedexNumber", mappedBy="species", cascade={"ALL"},
     *     orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"isDefault" = "DESC"})
     * @Groups({"read"})
     */
    private Collection $numbers;

    /**
     * @var Collection<Pokemon>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="species", cascade={"ALL"}, orphanRemoval=true)
     * @ORM\OrderBy({"isDefault" = "DESC"})
     */
    private Collection $pokemon;

    public function __construct()
    {
        $this->numbers = new ArrayCollection();
        $this->pokemon = new ArrayCollection();
    }

    /**
     * @return Collection<PokemonSpeciesPokedexNumber>
     */
    public function getNumbers(): Collection
    {
        return $this->numbers;
    }

    /**
     * @return int
     * @Groups({"read"})
     */
    public function getNationalDexNumber(): int
    {
        foreach ($this->numbers as $number) {
            if ($number->getPokedex()->getSlug() === 'national') {
                return $number->getNumber();
            }
        }

        return 0;
    }

    public function addNumber(PokemonSpeciesPokedexNumber $number): self
    {
        if (!$this->numbers->contains($number)) {
            $this->numbers->add($number);
            $number->setSpecies($this);
        }

        return $this;
    }

    public function removeNumber(PokemonSpeciesPokedexNumber $number): self
    {
        if ($this->numbers->contains($number)) {
            $this->numbers->removeElement($number);
        }

        return $this;
    }

    public function addPokemon(Pokemon $pokemon): self
    {
        if (!$this->pokemon->contains($pokemon)) {
            $this->pokemon->add($pokemon);
            $pokemon->setSpecies($this);
        }

        return $this;
    }

    public function removePokemon(Pokemon $pokemon): self
    {
        if ($this->pokemon->contains($pokemon)) {
            $this->pokemon->removeElement($pokemon);
        }

        return $this;
    }

    /**
     * @return Pokemon
     * @Groups({"read"})
     */
    public function getDefaultPokemon(): Pokemon
    {
        foreach ($this->getPokemon() as $pokemon) {
            if ($pokemon->isDefault()) {
                return $pokemon;
            }
        }

        return $this->getPokemon()->first();
    }

    /**
     * @return Collection<Pokemon>
     */
    public function getPokemon(): Collection
    {
        return $this->pokemon;
    }
}
