<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pokémon are defined as a form with different types, moves, or other game-
 * changing properties.
 *
 * e.g. There are four separate "Pokemon" for Deoxys, but only one for Unown.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonRepository")
 * @Gedmo\Tree(type="materializedPath")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['species.position' => 'ASC', 'position' => 'ASC'],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'abilities.ability' => 'exact',
    'eggGroups' => 'exact',
    'slug' => 'exact',
    'species' => 'exact',
    'species.versionGroup' => 'exact',
    'types.type' => 'exact',
])]
#[ApiFilter(BooleanFilter::class, properties: [
    'mega',
    'baby',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'abilities.ability.position',
    'attack.baseValue',
    'defense.baseValue',
    'hp.baseValue',
    'name',
    'position',
    'special.baseValue',
    'specialAttack.baseValue',
    'specialDefense.baseValue',
    'species.position',
    'speed.baseValue',
    'statTotal',
    'types.type.position',
])]
class Pokemon extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface, EntityHasDefaultInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
    use EntityHasDefaultTrait;

    /**
     * Unique Id
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Gedmo\TreePathSource()
     * @Groups({"read"})
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonSpeciesInVersionGroup", inversedBy="pokemon")
     */
    private PokemonSpeciesInVersionGroup $species;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\TreePath()
     */
    private ?string $evolutionPath;

    /**
     * The Pokémon from which this one evolves
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="evolutionChildren")
     * @Gedmo\TreeParent()
     */
    private ?Pokemon $evolutionParent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\TreeLevel()
     */
    private ?int $evolutionStage;

    /**
     * @var Collection<Pokemon>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="evolutionParent")
     */
    private Collection $evolutionChildren;

    /**
     * @var Collection<PokemonEvolutionCondition>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonEvolutionCondition", mappedBy="pokemon", cascade={"ALL"},
     *     orphanRemoval=true)
     */
    private Collection $evolutionConditions;

    /**
     * This Pokémon’s Pokédex color, as used for a search function in the games.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonColor")
     * @Groups({"pokemon_view"})
     */
    private ?PokemonColor $color;

    /**
     * This Pokémon’s body shape, as used for a search function in the games.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonShapeInVersionGroup")
     * @Groups({"pokemon_view"})
     */
    private ?PokemonShapeInVersionGroup $shape;

    /**
     * This Pokémon’s habitat, as used for a search function in the games.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonHabitat")
     * @Groups({"pokemon_view"})
     */
    private ?PokemonHabitat $habitat;

    /**
     * The chance of this Pokémon being female; null if genderless.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="100")
     * @Groups({"pokemon_view"})
     */
    private ?int $femaleRate;

    /**
     * The base capture rate
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="255")
     * @Groups({"pokemon_view", "capture_rate"})
     */
    private int $captureRate;

    /**
     * The tameness when caught by a normal ball
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="255")
     * @Groups({"pokemon_view"})
     */
    private ?int $happiness;

    /**
     * True if the Pokémon is a baby.
     *
     * A baby is a lowest-stage Pokémon that cannot breed but whose evolved form
     * can.
     *
     * @ORM\Column(type="boolean")
     * @Groups({"pokemon_view"})
     */
    private bool $baby = false;

    /**
     * Initial hatch counter
     *
     * The exact formula varies by game, however this number of steps will
     * always be a part of that formula.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"pokemon_view"})
     */
    private ?int $hatchSteps;

    /**
     * The growth rate for this Pokémon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\GrowthRate")
     * @Groups({"pokemon_view"})
     */
    private GrowthRate $growthRate;

    /**
     * True if a particular individual of this species can switch between its
     * different forms at will
     *
     * @ORM\Column(type="boolean")
     * @Groups({"pokemon_view"})
     */
    private bool $formsSwitchable = false;

    /**
     * The short flavor text, such as "Seed" or "Lizard"; usually affixed with
     * the word "Pokémon"
     *
     * @ORM\Column(type="text")
     * @Groups({"pokemon_view"})
     */
    private string $genus;

    /**
     * Description of how the forms work
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"pokemon_view"})
     */
    #[MarkdownProperty]
    private ?string $formsNote;

    /**
     * @var Collection<PokemonFlavorText>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonFlavorText", mappedBy="pokemon", cascade={"ALL"},
     *     orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"pokemon_view"})
     */
    private Collection $flavorText;

    /**
     * @var Collection<EggGroup>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EggGroup")
     * @Groups({"pokemon_view"})
     */
    private Collection $eggGroups;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PokemonPalParkData", mappedBy="pokemon", cascade={"ALL"},
     *     orphanRemoval=true)
     * @Groups({"pokemon_view"})
     */
    private ?PokemonPalParkData $palParkData = null;

    /**
     * @ORM\Column(type="safe_object")
     */
    private Length $height;

    /**
     * @ORM\Column(type="safe_object")
     */
    private Mass $weight;

    /**
     * The base EXP gained when defeating this Pokémon
     *
     * @ORM\Column(type="integer")
     * @Groups({"pokemon_view"})
     */
    private int $experience;

    /**
     * @var Collection<PokemonAbility>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonAbility", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read"})
     */
    private Collection $abilities;

    /**
     * @var Collection<PokemonWildHeldItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonWildHeldItem", mappedBy="pokemon", cascade={"ALL"},
     *     orphanRemoval=true)
     * @Groups({"pokemon_view"})
     */
    private Collection $wildHeldItems;

    /**
     * @ORM\Embedded(class="App\Entity\PokemonStat")
     * @Groups({"read"})
     */
    private ?PokemonStat $hp = null;

    /**
     * @ORM\Embedded(class="App\Entity\PokemonStat")
     * @Groups({"read"})
     */
    private ?PokemonStat $attack = null;

    /**
     * @ORM\Embedded(class="App\Entity\PokemonStat")
     * @Groups({"read"})
     */
    private ?PokemonStat $defense = null;

    /**
     * @ORM\Embedded(class="App\Entity\PokemonStat")
     * @Groups({"read"})
     */
    private ?PokemonStat $specialAttack = null;

    /**
     * @ORM\Embedded(class="App\Entity\PokemonStat")
     * @Groups({"read"})
     */
    private ?PokemonStat $specialDefense = null;

    /**
     * @ORM\Embedded(class="App\Entity\PokemonStat")
     * @Groups({"read"})
     */
    private ?PokemonStat $special = null;

    /**
     * @ORM\Embedded(class="App\Entity\PokemonStat")
     * @Groups({"read"})
     */
    private ?PokemonStat $speed = null;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private int $statTotal = 0;

    /**
     * @var Collection<PokemonType>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonType", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true,
     *     fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read"})
     */
    private Collection $types;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private bool $mega = false;

    /**
     * @var Collection<PokemonForm>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonForm", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true)
     * @ORM\OrderBy({"isDefault" = "DESC", "position" = "ASC"})
     */
    private Collection $forms;


    public function __construct()
    {
        $this->evolutionChildren = new ArrayCollection();
        $this->evolutionConditions = new ArrayCollection();
        $this->flavorText = new ArrayCollection();
        $this->eggGroups = new ArrayCollection();
        $this->abilities = new ArrayCollection();
        $this->wildHeldItems = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->forms = new ArrayCollection();
    }

    public function getEvolutionPath(): ?string
    {
        return $this->evolutionPath;
    }

    public function setEvolutionPath(?string $evolutionPath): self
    {
        $this->evolutionPath = $evolutionPath;

        return $this;
    }

    public function getEvolutionParent(): ?Pokemon
    {
        return $this->evolutionParent;
    }

    public function setEvolutionParent(?Pokemon $evolutionParent): self
    {
        $this->evolutionParent = $evolutionParent;

        return $this;
    }

    public function getEvolutionFamily(): array
    {
        return $this->calcEvolutionFamily();
    }

    private function calcEvolutionFamily(array &$family = []): array
    {
        if (empty($family)) {
            $root = $this->getEvolutionRoot();
            $family[] = $root;
            foreach ($root->getEvolutionChildren() as $child) {
                $child->calcEvolutionFamily($family);
            }
        } else {
            $family[] = $this;

            foreach ($this->evolutionChildren as $evolutionChild) {
                $evolutionChild->calcEvolutionFamily($family);
            }
        }

        return $family;
    }

    private function getEvolutionRoot(): Pokemon
    {
        if (isset($this->evolutionParent)) {
            return $this->evolutionParent->getEvolutionRoot();
        }

        return $this;
    }

    /**
     * @return Collection<Pokemon>
     */
    public function getEvolutionChildren(): Collection
    {
        return $this->evolutionChildren;
    }

    public function getEvolutionStage(): ?int
    {
        return $this->evolutionStage;
    }

    /**
     * @return Collection<PokemonEvolutionCondition>
     */
    public function getEvolutionConditions(): Collection
    {
        return $this->evolutionConditions;
    }

    public function addEvolutionCondition(PokemonEvolutionCondition $evolutionCondition): self
    {
        if (!$this->evolutionConditions->contains($evolutionCondition)) {
            $this->evolutionConditions->add($evolutionCondition);
            $evolutionCondition->setPokemon($this);
        }

        return $this;
    }

    public function removeEvolutionCondition(PokemonEvolutionCondition $evolutionCondition): self
    {
        if ($this->evolutionConditions->contains($evolutionCondition)) {
            $this->evolutionConditions->removeElement($evolutionCondition);
        }

        return $this;
    }

    public function getColor(): ?PokemonColor
    {
        return $this->color;
    }

    public function setColor(?PokemonColor $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getShape(): ?PokemonShapeInVersionGroup
    {
        return $this->shape;
    }

    public function setShape(?PokemonShapeInVersionGroup $shape): self
    {
        $this->shape = $shape;

        return $this;
    }

    public function getHabitat(): ?PokemonHabitat
    {
        return $this->habitat;
    }

    public function setHabitat(?PokemonHabitat $habitat): self
    {
        $this->habitat = $habitat;

        return $this;
    }

    public function getFemaleRate(): ?int
    {
        return $this->femaleRate;
    }

    public function setFemaleRate(?int $femaleRate): self
    {
        $this->femaleRate = $femaleRate;

        return $this;
    }

    public function getCaptureRate(): ?int
    {
        return $this->captureRate;
    }

    public function setCaptureRate(int $captureRate): self
    {
        $this->captureRate = $captureRate;

        return $this;
    }

    public function getHappiness(): ?int
    {
        return $this->happiness;
    }

    public function setHappiness(?int $happiness): self
    {
        $this->happiness = $happiness;

        return $this;
    }

    public function isBaby(): bool
    {
        return $this->baby;
    }

    public function setBaby(bool $baby): self
    {
        $this->baby = $baby;

        return $this;
    }

    public function getHatchSteps(): ?int
    {
        return $this->hatchSteps;
    }

    public function setHatchSteps(?int $hatchSteps): self
    {
        $this->hatchSteps = $hatchSteps;

        return $this;
    }

    public function getGrowthRate(): ?GrowthRate
    {
        return $this->growthRate;
    }

    public function setGrowthRate(GrowthRate $growthRate): self
    {
        $this->growthRate = $growthRate;

        return $this;
    }

    public function isFormsSwitchable(): bool
    {
        return $this->formsSwitchable;
    }

    public function setFormsSwitchable(bool $formsSwitchable): self
    {
        $this->formsSwitchable = $formsSwitchable;

        return $this;
    }

    public function getGenus(): ?string
    {
        return $this->genus;
    }

    public function setGenus(string $genus): self
    {
        $this->genus = $genus;

        return $this;
    }

    public function getFormsNote(): ?string
    {
        return $this->formsNote;
    }

    public function setFormsNote(?string $formsNote): self
    {
        $this->formsNote = $formsNote;

        return $this;
    }


    public function getFlavorTextInVersion(Version $version): ?PokemonFlavorText
    {
        foreach ($this->getFlavorText() as $flavorText) {
            if ($flavorText->getVersion() === $version) {
                return $flavorText;
            }
        }

        return null;
    }

    /**
     * @return Collection<PokemonFlavorText>
     */
    public function getFlavorText(): Collection
    {
        return $this->flavorText;
    }

    public function addFlavorText(PokemonFlavorText $flavorText): self
    {
        if (!$this->flavorText->contains($flavorText)) {
            $this->flavorText->add($flavorText);
            $flavorText->setPokemon($this);
        }

        return $this;
    }

    public function removeFlavorText(PokemonFlavorText $flavorText): self
    {
        if ($this->flavorText->contains($flavorText)) {
            $this->flavorText->removeElement($flavorText);
        }

        return $this;
    }

    /**
     * @return Collection<EggGroup>
     */
    public function getEggGroups(): Collection
    {
        return $this->eggGroups;
    }

    public function addEggGroup(EggGroup $eggGroup): self
    {
        if (!$this->eggGroups->contains($eggGroup)) {
            $this->eggGroups->add($eggGroup);
        }

        return $this;
    }

    public function removeEggGroup(EggGroup $eggGroup): self
    {
        if ($this->eggGroups->contains($eggGroup)) {
            $this->eggGroups->removeElement($eggGroup);
        }

        return $this;
    }

    public function getPalParkData(): ?PokemonPalParkData
    {
        return $this->palParkData;
    }

    public function setPalParkData(?PokemonPalParkData $palParkData): self
    {
        $this->palParkData = $palParkData;
        if ($palParkData !== null) {
            $palParkData->setPokemon($this);
        }

        return $this;
    }

    public function getHeight(): ?Length
    {
        return $this->height;
    }

    /**
     * @Groups({"pokemon_view"})
     */
    public function getHeightCentimeters(): ?float
    {
        return $this->height ? $this->height->toUnit('cm') : null;
    }

    public function setHeight(Length $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?Mass
    {
        return $this->weight;
    }

    /**
     * @Groups({"pokemon_view", "capture_rate"})
     */
    public function getWeightGrams(): ?float
    {
        return $this->weight ? $this->weight->toUnit('g') : null;
    }

    public function setWeight(Mass $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getExperience(): ?int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getAbilityData(AbilityInVersionGroup $ability): ?PokemonAbility
    {
        foreach ($this->getAbilities() as $pokemonAbility) {
            if ($pokemonAbility->getAbility() === $ability) {
                return $pokemonAbility;
            }
        }

        return null;
    }

    /**
     * @return Collection<PokemonAbility>
     */
    public function getAbilities(): Collection
    {
        return $this->abilities;
    }

    public function getSpecies(): ?PokemonSpeciesInVersionGroup
    {
        return $this->species;
    }

    /**
     * @Groups({"read", "capture_rate"})
     */
    public function getSpeciesSlug(): ?string
    {
        return $this->species->getSlug();
    }

    public function setSpecies(PokemonSpeciesInVersionGroup $species): self
    {
        $this->species = $species;

        return $this;
    }

    public function addAbility(PokemonAbility $ability): self
    {
        if (!$this->abilities->contains($ability)) {
            $this->abilities->add($ability);
            $ability->setPokemon($this);
        }

        return $this;
    }

    public function removeAbility(PokemonAbility $ability): self
    {
        if ($this->abilities->contains($ability)) {
            $this->abilities->removeElement($ability);
        }

        return $this;
    }

    /**
     * @param Version $version
     *
     * @return Collection<PokemonWildHeldItem>
     */
    public function getWildHeldItemsInVersion(Version $version): Collection
    {
        return $this->getWildHeldItems()->filter(
            function (PokemonWildHeldItem $wildHeldItem) use ($version) {
                return ($wildHeldItem->getVersion() === $version);
            }
        );
    }

    /**
     * @return Collection<PokemonWildHeldItem
     */
    public function getWildHeldItems(): Collection
    {
        return $this->wildHeldItems;
    }

    public function addWildHeldItem(PokemonWildHeldItem $heldItem): self
    {
        if (!$this->wildHeldItems->contains($heldItem)) {
            $this->wildHeldItems->add($heldItem);
            $heldItem->setPokemon($this);
        }

        return $this;
    }

    public function removeWildHeldItem(PokemonWildHeldItem $heldItem): self
    {
        if ($this->wildHeldItems->contains($heldItem)) {
            $this->wildHeldItems->removeElement($heldItem);
        }

        return $this;
    }

    /**
     * @return PokemonStat|null
     */
    public function getHp(): ?PokemonStat
    {
        return $this->hp;
    }

    /**
     * @param PokemonStat|null $hp
     *
     * @return Pokemon
     */
    public function setHp(?PokemonStat $hp): Pokemon
    {
        $this->hp = $hp;

        return $this;
    }

    /**
     * @return PokemonStat|null
     */
    public function getAttack(): ?PokemonStat
    {
        return $this->attack;
    }

    /**
     * @param PokemonStat|null $attack
     *
     * @return Pokemon
     */
    public function setAttack(?PokemonStat $attack): Pokemon
    {
        $this->attack = $attack;

        return $this;
    }

    /**
     * @return PokemonStat|null
     */
    public function getDefense(): ?PokemonStat
    {
        return $this->defense;
    }

    /**
     * @param PokemonStat|null $defense
     *
     * @return Pokemon
     */
    public function setDefense(?PokemonStat $defense): Pokemon
    {
        $this->defense = $defense;

        return $this;
    }

    /**
     * @return PokemonStat|null
     */
    public function getSpecialAttack(): ?PokemonStat
    {
        return $this->specialAttack;
    }

    /**
     * @param PokemonStat|null $specialAttack
     *
     * @return Pokemon
     */
    public function setSpecialAttack(?PokemonStat $specialAttack): Pokemon
    {
        $this->specialAttack = $specialAttack;

        return $this;
    }

    /**
     * @return PokemonStat|null
     */
    public function getSpecialDefense(): ?PokemonStat
    {
        return $this->specialDefense;
    }

    /**
     * @param PokemonStat|null $specialDefense
     *
     * @return Pokemon
     */
    public function setSpecialDefense(?PokemonStat $specialDefense): Pokemon
    {
        $this->specialDefense = $specialDefense;

        return $this;
    }

    /**
     * @return PokemonStat|null
     */
    public function getSpecial(): ?PokemonStat
    {
        return $this->special;
    }

    /**
     * @param PokemonStat|null $special
     *
     * @return Pokemon
     */
    public function setSpecial(?PokemonStat $special): Pokemon
    {
        $this->special = $special;

        return $this;
    }

    /**
     * @return PokemonStat|null
     */
    public function getSpeed(): ?PokemonStat
    {
        return $this->speed;
    }

    /**
     * @param PokemonStat|null $speed
     *
     * @return Pokemon
     */
    public function setSpeed(?PokemonStat $speed): Pokemon
    {
        $this->speed = $speed;

        return $this;
    }

    public function getStat(string $statSlug): ?PokemonStat
    {
        return match ($statSlug) {
            'hp' => $this->hp,
            'attack' => $this->attack,
            'defense' => $this->defense,
            'special-attack' => $this->specialAttack,
            'special-defense' => $this->specialDefense,
            'special' => $this->special,
            'speed' => $this->speed,
            default => throw new \ValueError('Invalid stat slug '.$statSlug),
        };
    }

    /**
     * @return int
     */
    public function getStatTotal(): int
    {
        return $this->statTotal;
    }

    /**
     * @param int $statTotal
     *
     * @return Pokemon
     */
    public function setStatTotal(int $statTotal): Pokemon
    {
        $this->statTotal = $statTotal;

        return $this;
    }

    /**
     * @return Collection<PokemonType>
     */
    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function addType(PokemonType $type): self
    {
        if (!$this->types->contains($type)) {
            $this->types->add($type);
            $type->setPokemon($this);
        }

        return $this;
    }

    public function removeType(PokemonType $type): self
    {
        if ($this->types->contains($type)) {
            $this->types->removeElement($type);
        }

        return $this;
    }

    public function isMega(): bool
    {
        return $this->mega;
    }

    public function setMega(bool $mega): self
    {
        $this->mega = $mega;

        return $this;
    }

    public function addForm(PokemonForm $form): self
    {
        if (!$this->forms->contains($form)) {
            $this->forms->add($form);
            $form->setPokemon($this);
        }

        return $this;
    }

    public function removeForm(PokemonForm $form): self
    {
        if ($this->forms->contains($form)) {
            $this->forms->removeElement($form);
        }

        return $this;
    }

    /**
     * @return PokemonForm
     * @Groups({"read", "capture_rate"})
     */
    public function getDefaultForm(): PokemonForm
    {
        foreach ($this->getForms() as $form) {
            if ($form->isDefault()) {
                return $form;
            }
        }

        return $this->getForms()->first();
    }

    /**
     * @return Collection<PokemonForm>
     */
    public function getForms(): Collection
    {
        return $this->forms;
    }
}
