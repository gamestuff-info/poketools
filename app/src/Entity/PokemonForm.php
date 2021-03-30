<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use App\Entity\Media\PokemonArt;
use App\Entity\Media\PokemonSprite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An individual form of a Pokémon.
 *
 * This includes every variant (except color differences) of every Pokémon,
 * regardless of how the games treat them. Even Pokémon with no alternate forms
 * have one form to represent their lone "normal" form.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonFormRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['pokemon.position' => 'ASC', 'position' => 'ASC'],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'pokemon' => 'exact',
    'pokemon.species.versionGroup' => 'exact',
    'slug' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
    'pokemon.species.position' => 'ASC',
    'pokemon.position' => 'ASC',
    'position' => 'ASC',
])]
class PokemonForm extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface, EntityIsSortableInterface, EntityHasIconInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;
    use EntityIsSortableTrait;
    use EntityHasIconTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="forms")
     */
    private Pokemon $pokemon;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private string $formName;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private bool $battleOnly = false;

    /**
     * @var Collection<PokemonFormPokeathlonStat>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonFormPokeathlonStat", mappedBy="pokemonForm", cascade={"ALL"},
     *     orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"pokemon_view"})
     */
    private Collection $pokeathlonStats;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     * @Groups({"pokemon_view"})
     */
    private ?string $cry;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     * @Groups({"pokemon_view"})
     */
    private ?string $footprint;

    /**
     * @var Collection<PokemonSprite>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Media\PokemonSprite", mappedBy="pokemonForm", cascade={"ALL"},
     *     orphanRemoval=true, fetch="EAGER")
     * @Groups({"pokemon_view"})
     */
    private Collection $sprites;

    /**
     * @var Collection<PokemonArt>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Media\PokemonArt", mappedBy="pokemonForm", cascade={"ALL"},
     *     orphanRemoval=true, fetch="EAGER")
     * @Groups({"pokemon_view"})
     */
    private Collection $art;

    public function __construct()
    {
        $this->pokeathlonStats = new ArrayCollection();
        $this->sprites = new ArrayCollection();
        $this->art = new ArrayCollection();
    }

    /**
     * @Groups({"read"})
     */
    public function getSpeciesSlug(): ?string
    {
        return $this->pokemon->getSpeciesSlug();
    }

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    /**
     * @Groups({"read"})
     */
    public function getPokemonSlug(): ?string
    {
        return $this->pokemon->getSlug();
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getFormName(): ?string
    {
        return $this->formName;
    }

    public function setFormName(string $formName): self
    {
        $this->formName = $formName;

        return $this;
    }

    public function isBattleOnly(): ?bool
    {
        return $this->battleOnly;
    }

    public function setBattleOnly(bool $battleOnly): self
    {
        $this->battleOnly = $battleOnly;

        return $this;
    }

    public function getPokeathlonStatData(PokeathlonStat $pokeathlonStat): ?PokemonFormPokeathlonStat
    {
        foreach ($this->getPokeathlonStats() as $checkPokeathlonStat) {
            if ($checkPokeathlonStat->getPokeathlonStat() === $pokeathlonStat) {
                return $checkPokeathlonStat;
            }
        }

        return null;
    }

    /**
     * @return Collection<PokemonFormPokeathlonStat>
     */
    public function getPokeathlonStats(): Collection
    {
        return $this->pokeathlonStats;
    }

    public function getPokeathlonStatTotal(): int
    {
        $total = 0;

        foreach ($this->getPokeathlonStats() as $pokeathlonStat) {
            $total += $pokeathlonStat->getBaseValue();
        }

        return $total;
    }

    public function addPokeathlonStat(PokemonFormPokeathlonStat $pokeathlonStat): self
    {
        if (!$this->pokeathlonStats->contains($pokeathlonStat)) {
            $this->pokeathlonStats->add($pokeathlonStat);
            $pokeathlonStat->setPokemonForm($this);
        }

        return $this;
    }

    public function removePokeathlonStat(PokemonFormPokeathlonStat $pokeathlonStat): self
    {
        if ($this->pokeathlonStats->contains($pokeathlonStat)) {
            $this->pokeathlonStats->removeElement($pokeathlonStat);
        }

        return $this;
    }

    public function getCry(): ?string
    {
        return $this->cry;
    }

    public function setCry(?string $cry): self
    {
        $this->cry = $cry;

        return $this;
    }

    public function getFootprint(): ?string
    {
        return $this->footprint;
    }

    public function setFootprint(?string $footprint): self
    {
        $this->footprint = $footprint;

        return $this;
    }

    public function addSprite(PokemonSprite $sprite): self
    {
        $matches = $this->filterMatchingSprites($sprite);
        if ($matches->count() === 0) {
            $this->sprites->add($sprite);
            $sprite->setPokemonForm($this);
        }

        return $this;
    }

    /**
     * @param PokemonSprite $sprite
     *
     * @return Collection
     */
    private function filterMatchingSprites(PokemonSprite $sprite): Collection
    {
        return $this->getSprites()->filter(
            function (PokemonSprite $pokemonSprite) use ($sprite) {
                return $pokemonSprite->getUrl() === $sprite->getUrl();
            }
        );
    }

    /**
     * @return Collection<PokemonSprite>
     */
    public function getSprites(): Collection
    {
        return $this->sprites;
    }

    public function getDefaultSprite(): ?PokemonSprite
    {
        if ($this->sprites->isEmpty()) {
            return null;
        }

        return $this->sprites->first();
    }

    public function removeSprite(PokemonSprite $sprite): self
    {
        $matches = $this->filterMatchingSprites($sprite);
        if ($matches->count() > 0) {
            foreach ($matches as $match) {
                $this->sprites->removeElement($match);
            }
        }

        return $this;
    }

    public function addArt(PokemonArt $art): self
    {
        $matches = $this->filterMatchingArt($art);
        if ($matches->count() === 0) {
            $this->art->add($art);
            $art->setPokemonForm($this);
        }

        return $this;
    }

    private function filterMatchingArt(PokemonArt $art): Collection
    {
        $matches = $this->getArt()->filter(
            function (PokemonArt $pokemonSprite) use ($art) {
                return $pokemonSprite->getUrl() === $art->getUrl();
            }
        );

        return $matches;
    }

    /**
     * @return Collection<PokemonArt>
     */
    public function getArt(): Collection
    {
        return $this->art;
    }

    public function removeArt(PokemonArt $art): self
    {
        $matches = $this->filterMatchingArt($art);
        if ($matches->count() > 0) {
            foreach ($matches as $match) {
                $this->art->removeElement($match);
            }
        }

        return $this;
    }
}
