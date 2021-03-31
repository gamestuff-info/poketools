<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Entity\Embeddable\Range;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A technique or attack a Pokémon can learn to use.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveInVersionGroupRepository")
 *
 * @method Move getParent()
 * @method self setParent(Move $parent)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
    subresourceOperations: [
        'api_move_in_version_groups_contest_use_befores_get_subresource' => [
            'pagination_client_enabled' => true,
        ],
        'api_move_in_version_groups_contest_use_afters_get_subresource' => [
            'pagination_client_enabled' => true,
        ],
        'api_move_in_version_groups_super_contest_use_befores_get_subresource' => [
            'pagination_client_enabled' => true,
        ],
        'api_move_in_version_groups_super_contest_use_afters_get_subresource' => [
            'pagination_client_enabled' => true,
        ],
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'contestType' => 'exact',
    'effect' => 'exact',
    'flags.slug' => 'exact',
    'slug' => 'exact',
    'type' => 'exact',
    'versionGroup' => 'exact',
    'versionGroup.versions' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
    'type.position',
    'contestType.position',
    'damageClass.position',
    'type.damageClass.position',
    'pp',
    'power',
    'accuracy',
])]
#[ApiFilter(PropertyFilter::class)]
class MoveInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityHasFlavorTextInterface
{

    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityHasFlavorTextTrait;

    /**
     * @var Move
     * @ORM\ManyToOne(targetEntity="App\Entity\Move", inversedBy="children")
     */
    protected EntityHasChildrenInterface $parent;

    /**
     * The move’s elemental type
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private Type $type;

    /**
     * Base power of the move, null if it does not have a set base power.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $power;

    /**
     * Base PP (Power Points) of the move, null if not applicable (e.g. Struggle
     * and Shadow moves).
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $pp;

    /**
     * Accuracy of the move; NULL means it never misses.
     *
     * There is an important distinction between 100% and NULL accuracy - 100%
     * accuracy is still affected by other accuracy reductions.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="100")
     * @Groups({"read"})
     */
    private ?int $accuracy;

    /**
     * The move’s priority bracket
     *
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private int $priority;

    /**
     * The target (range) of the move
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveTarget", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"move_view"})
     */
    private MoveTarget $target;

    /**
     * The damage class (physical/special) of the move.
     *
     * Before Generation 4, this is taken from the move's type.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveDamageClass", fetch="EAGER")
     */
    private ?MoveDamageClass $damageClass;

    /**
     * The move’s effect
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveEffectInVersionGroup", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private MoveEffectInVersionGroup $effect;

    /**
     * The chance for a secondary effect. What this is a chance of is specified
     * by the move’s effect.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $effectChance;

    /**
     * The Move’s Contest type (e.g. cool or smart), if applicable in this
     * version group.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ContestType", fetch="EAGER")
     * @Groups({"read"})
     */
    private ?ContestType $contestType;

    /**
     * The move’s Contest effect, if applicable in this version group.
     *
     * @ORM\ManyToOne(targetEntity="ContestEffectInVersionGroup", fetch="EAGER")
     * @Groups({"read"})
     */
    private ?ContestEffectInVersionGroup $contestEffect;

    /**
     * The move’s Super Contest effect, if applicable in this version group.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\SuperContestEffect", fetch="EAGER")
     * @Groups({"read"})
     */
    private ?SuperContestEffect $superContestEffect;

    /**
     * Use this move before these moves for a Contest Combo.
     *
     * @var Collection<MoveInVersionGroup>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveInVersionGroup", inversedBy="contestUseAfter")
     * @ORM\JoinTable(
     *     name="move_in_version_group_contest_combo",
     *     joinColumns={@ORM\JoinColumn(name="move_in_version_group_first_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="move_in_version_group_second_id", referencedColumnName="id")}
     * )
     * @Groups({"move_view"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ApiSubresource(maxDepth: 1)]
    private Collection $contestUseBefore;

    /**
     * Use this move after these moves for a Contest Combo.
     *
     * @var Collection<MoveInVersionGroup>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveInVersionGroup", mappedBy="contestUseBefore")
     * @Groups({"move_view"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ApiSubresource(maxDepth: 1)]
    private Collection $contestUseAfter;

    /**
     * Use this move before these moves for a Super Contest Combo.
     *
     * @var Collection<MoveInVersionGroup>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveInVersionGroup", inversedBy="superContestUseAfter")
     * @ORM\JoinTable(
     *     name="move_in_version_group_super_contest_combo",
     *     joinColumns={@ORM\JoinColumn(name="move_in_version_group_first_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="move_in_version_group_second_id", referencedColumnName="id")}
     * )
     * @Groups({"move_view"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ApiSubresource(maxDepth: 1)]
    private Collection $superContestUseBefore;

    /**
     * Use this move after these moves for a Super Contest Combo.
     *
     * @var Collection<MoveInVersionGroup>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveInVersionGroup", mappedBy="superContestUseBefore")
     * @Groups({"move_view"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ApiSubresource(maxDepth: 1)]
    private Collection $superContestUseAfter;

    /**
     * TM/HM Data
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Machine", mappedBy="move", cascade={"all"})
     * @Groups({"move_view"})
     */
    private ?Machine $machine;

    /**
     * Move flags
     *
     * @var Collection<MoveFlag>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveFlag", fetch="EAGER")
     * @Groups({"move_view"})
     */
    private Collection $flags;

    /**
     * Stat changes moves (may) make.
     *
     * @var Collection<MoveStatChange>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MoveStatChange", mappedBy="move", cascade={"ALL"}, fetch="EAGER")
     * @Groups({"move_view"})
     */
    private Collection $statChanges;

    /**
     * Chance of causing a stat change
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"move_view"})
     */
    private ?int $statChangeChance;

    /**
     * Move Categories
     *
     * @var Collection<MoveCategory>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveCategory", fetch="EAGER")
     * @Groups({"move_view"})
     */
    private Collection $categories;

    /**
     * Ailment this move can cause
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveAilment", fetch="EAGER")
     * @Groups({"move_view"})
     */
    private ?MoveAilment $ailment;

    /**
     * Chance of causing an ailment, null if this move cannot cause an ailment.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="100")
     * @Groups({"move_view"})
     */
    private ?int $ailmentChance;

    /**
     * Number of hits this move can inflict
     *
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     * @Groups({"move_view"})
     */
    private Range $hits;

    /**
     * Number of turns the user is forced to use this move
     *
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     * @Groups({"move_view"})
     */
    private Range $turns;

    /**
     * HP drain, in percent of damage dealt.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"move_view"})
     */
    private ?int $drain;

    /**
     * Recoil damage, in percent of damage dealt.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"move_view"})
     */
    private ?int $recoil;

    /**
     * Healing, in percent of the user's max HP.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"move_view"})
     */
    private ?int $healing;

    /**
     * Critical hit rate bonus, if any
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"move_view"})
     */
    private ?int $critRateBonus;

    /**
     * Chance of causing flinching
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"move_view"})
     */
    private ?int $flinchChance;

    public function __construct()
    {
        $this->contestUseBefore = new ArrayCollection();
        $this->contestUseAfter = new ArrayCollection();
        $this->superContestUseBefore = new ArrayCollection();
        $this->superContestUseAfter = new ArrayCollection();
        $this->flags = new ArrayCollection();
        $this->statChanges = new ArrayCollection();
        $this->categories = new ArrayCollection();
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

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(?int $power): self
    {
        $this->power = $power;

        return $this;
    }

    public function getPp(): ?int
    {
        return $this->pp;
    }

    public function setPp(?int $pp): self
    {
        $this->pp = $pp;

        return $this;
    }

    public function getAccuracy(): ?int
    {
        return $this->accuracy;
    }

    public function setAccuracy(?int $accuracy): self
    {
        $this->accuracy = $accuracy;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getTarget(): ?MoveTarget
    {
        return $this->target;
    }

    public function setTarget(MoveTarget $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getDamageClass(): ?MoveDamageClass
    {
        return $this->damageClass;
    }

    /**
     * Get the damage class from the move or type depending on the version.
     *
     * @return MoveDamageClass|null
     * @Groups({"read"})
     */
    public function getEffectiveDamageClass(): ?MoveDamageClass
    {
        if ($this->versionGroup->hasFeatureString('move-damage-class')) {
            return $this->damageClass;
        } else {
            return $this->type->getDamageClass();
        }
    }

    public function setDamageClass(?MoveDamageClass $damageClass): self
    {
        $this->damageClass = $damageClass;

        return $this;
    }

    public function getEffect(): ?MoveEffectInVersionGroup
    {
        return $this->effect;
    }

    public function setEffect(MoveEffectInVersionGroup $effect): self
    {
        $this->effect = $effect;

        return $this;
    }

    public function getEffectChance(): ?int
    {
        return $this->effectChance;
    }

    public function setEffectChance(?int $effectChance): self
    {
        $this->effectChance = $effectChance;

        return $this;
    }

    public function getContestType(): ?ContestType
    {
        return $this->contestType;
    }

    public function setContestType(?ContestType $contestType): self
    {
        $this->contestType = $contestType;

        return $this;
    }

    public function getContestEffect(): ?ContestEffectInVersionGroup
    {
        return $this->contestEffect;
    }

    public function setContestEffect(?ContestEffectInVersionGroup $contestEffect): self
    {
        $this->contestEffect = $contestEffect;

        return $this;
    }

    public function getSuperContestEffect(): ?SuperContestEffect
    {
        return $this->superContestEffect;
    }

    public function setSuperContestEffect(?SuperContestEffect $superContestEffect): self
    {
        $this->superContestEffect = $superContestEffect;

        return $this;
    }

    /**
     * @return Collection<MoveInVersionGroup>
     */
    public function getContestUseBefore(): Collection
    {
        return $this->contestUseBefore;
    }

    /**
     * @return Collection<MoveInVersionGroup>
     */
    public function getContestUseAfter(): Collection
    {
        return $this->contestUseAfter;
    }

    public function addContestUseAfter(MoveInVersionGroup $move): self
    {
        if (!$this->contestUseAfter->contains($move)) {
            $this->contestUseAfter->add($move);
            $move->addContestUseBefore($this);
        }

        return $this;
    }

    public function addContestUseBefore(MoveInVersionGroup $move): self
    {
        if (!$this->contestUseBefore->contains($move)) {
            $this->contestUseBefore->add($move);
        }

        return $this;
    }

    public function removeContestUseAfter(MoveInVersionGroup $move): self
    {
        if ($this->contestUseAfter->contains($move)) {
            $this->contestUseAfter->removeElement($move);
            $move->removeContestUseBefore($this);
        }

        return $this;
    }

    public function removeContestUseBefore(MoveInVersionGroup $move): self
    {
        if ($this->contestUseBefore->contains($move)) {
            $this->contestUseBefore->removeElement($move);
        }

        return $this;
    }

    /**
     * @return Collection<MoveInVersionGroup>
     */
    public function getSuperContestUseBefore(): Collection
    {
        return $this->superContestUseBefore;
    }

    /**
     * @return Collection<MoveInVersionGroup>
     */
    public function getSuperContestUseAfter(): Collection
    {
        return $this->superContestUseAfter;
    }

    public function addSuperContestUseAfter(MoveInVersionGroup $move): self
    {
        if (!$this->superContestUseAfter->contains($move)) {
            $this->superContestUseAfter->add($move);
            $move->addSuperContestUseBefore($this);
        }

        return $this;
    }

    public function addSuperContestUseBefore(MoveInVersionGroup $move): self
    {
        if (!$this->superContestUseBefore->contains($move)) {
            $this->superContestUseBefore->add($move);
        }

        return $this;
    }

    public function removeSuperContestUseAfter(MoveInVersionGroup $move): self
    {
        if ($this->superContestUseAfter->contains($move)) {
            $this->superContestUseAfter->removeElement($move);
            $move->removeSuperContestUseBefore($this);
        }

        return $this;
    }

    public function removeSuperContestUseBefore(MoveInVersionGroup $move): self
    {
        if ($this->superContestUseBefore->contains($move)) {
            $this->superContestUseBefore->removeElement($move);
        }

        return $this;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): self
    {
        $this->machine = $machine;
        $machine->setMove($this);

        return $this;
    }

    /**
     * @return Collection<MoveFlag>
     */
    public function getFlags(): Collection
    {
        return $this->flags;
    }

    public function addFlag(MoveFlag $move): self
    {
        if (!$this->flags->contains($move)) {
            $this->flags->add($move);
        }

        return $this;
    }

    public function removeFlag(MoveFlag $move): self
    {
        if ($this->flags->contains($move)) {
            $this->flags->removeElement($move);
        }

        return $this;
    }

    /**
     * @return Collection<MoveStatChange>
     */
    public function getStatChanges(): Collection
    {
        return $this->statChanges;
    }

    public function addStatChange(MoveStatChange $statChange): self
    {
        if (!$this->statChanges->contains($statChange)) {
            $this->statChanges->add($statChange);
            $statChange->setMove($this);
        }

        return $this;
    }

    public function removeStatChange(MoveStatChange $statChange): self
    {
        if ($this->statChanges->contains($statChange)) {
            $this->statChanges->removeElement($statChange);
        }

        return $this;
    }

    public function getStatChangeChance(): ?int
    {
        return $this->statChangeChance;
    }

    public function setStatChangeChance(?int $statChangeChance): self
    {
        $this->statChangeChance = $statChangeChance;

        return $this;
    }

    /**
     * @return Collection<MoveCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(MoveCategory $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(MoveCategory $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    public function getAilment(): ?MoveAilment
    {
        return $this->ailment;
    }

    public function setAilment(?MoveAilment $ailment): self
    {
        $this->ailment = $ailment;

        return $this;
    }

    public function getAilmentChance(): ?int
    {
        return $this->ailmentChance;
    }

    public function setAilmentChance(?int $ailmentChance): self
    {
        $this->ailmentChance = $ailmentChance;

        return $this;
    }

    public function getHits(): Range
    {
        return $this->hits;
    }

    public function setHits(Range $hits): self
    {
        $this->hits = $hits;

        return $this;
    }

    public function getTurns(): Range
    {
        return $this->turns;
    }

    public function setTurns(Range $turns): self
    {
        $this->turns = $turns;

        return $this;
    }

    public function getDrain(): ?int
    {
        return $this->drain;
    }

    public function setDrain(?int $drain): self
    {
        $this->drain = $drain;

        return $this;
    }

    public function getRecoil(): ?int
    {
        return $this->recoil;
    }

    public function setRecoil(?int $recoil): self
    {
        $this->recoil = $recoil;

        return $this;
    }

    public function getHealing(): ?int
    {
        return $this->healing;
    }

    public function setHealing(?int $healing): self
    {
        $this->healing = $healing;

        return $this;
    }

    public function getCritRateBonus(): ?int
    {
        return $this->critRateBonus;
    }

    public function setCritRateBonus(?int $critRateBonus): self
    {
        $this->critRateBonus = $critRateBonus;

        return $this;
    }

    public function getFlinchChance(): ?int
    {
        return $this->flinchChance;
    }

    public function setFlinchChance(?int $flinchChance): self
    {
        $this->flinchChance = $flinchChance;

        return $this;
    }
}
