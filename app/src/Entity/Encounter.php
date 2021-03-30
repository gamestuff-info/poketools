<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use App\Entity\Embeddable\Range;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A single possible encounter with a pokemon
 *
 * This could be a pokemon encountered in the wild (e.g. tall grass), traded
 * or otherwise acquired from an NPC, or received as part of a scripted event.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EncounterRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
    // The data provider only allows this for locationArea or pokemon filters.
    paginationClientEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'locationArea' => 'exact',
    'method' => 'exact',
    'pokemon' => 'exact',
    'version' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'chance',
    'conditions.condition.position',
    'conditions.position',
    'level.max',
    'level.min',
    'locationArea.name',
    'method.position',
    'pokemon.abilities.ability.name',
    'pokemon.attack.baseValue',
    'pokemon.defense.baseValue',
    'pokemon.hp.baseValue',
    'pokemon.name',
    'pokemon.position',
    'pokemon.special.baseValue',
    'pokemon.specialAttack.baseValue',
    'pokemon.specialDefense.baseValue',
    'pokemon.species.position',
    'pokemon.speed.baseValue',
    'pokemon.statTotal',
    'pokemon.types.type.position',
    'position',
])]
class Encounter extends AbstractDexEntity implements EntityGroupedByVersionInterface, EntityIsSortableInterface
{
    use EntityGroupedByVersionTrait;
    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationArea", inversedBy="encounters")
     * @Assert\NotBlank()
     * @Groups({"pokemon_view"})
     */
    private LocationArea $locationArea;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EncounterMethod")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private EncounterMethod $method;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon")
     * @Assert\NotBlank()
     * @Groups({"location_view"})
     */
    private Pokemon $pokemon;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     * @Groups({"read"})
     */
    private ?Range $level;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="1", max="100")
     * @Groups({"read"})
     */
    private ?int $chance;

    /**
     * @var Collection<EncounterConditionState>
     * @ORM\ManyToMany(targetEntity="App\Entity\EncounterConditionState", fetch="EAGER")
     * @Groups({"read"})
     */
    private Collection $conditions;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read"})
     */
    #[MarkdownProperty]
    private ?string $note;

    public function __construct()
    {
        $this->conditions = new ArrayCollection();
        $this->level = new Range();
    }

    public function getLocationArea(): ?LocationArea
    {
        return $this->locationArea;
    }

    public function setLocationArea(LocationArea $locationArea): self
    {
        $this->locationArea = $locationArea;

        return $this;
    }

    public function getMethod(): ?EncounterMethod
    {
        return $this->method;
    }

    public function setMethod(EncounterMethod $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getLevel(): ?Range
    {
        return $this->level;
    }

    public function setLevel(?Range $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getChance(): ?int
    {
        return $this->chance;
    }

    public function setChance(?int $chance): self
    {
        $this->chance = $chance;

        return $this;
    }

    /**
     * @return Collection<EncounterConditionState>
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    public function addCondition(EncounterConditionState $conditionState): self
    {
        if (!$this->conditions->contains($conditionState)) {
            $this->conditions->add($conditionState);
        }

        return $this;
    }

    public function removeCondition(EncounterConditionState $conditionState): self
    {
        if ($this->conditions->contains($conditionState)) {
            $this->conditions->removeElement($conditionState);
        }

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }
}
