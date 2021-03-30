<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Item from the games, like "Poké Ball" or "Bicycle".
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemInVersionGroupRepository")
 *
 * @method Item getParent()
 * @method self setParent(Item $parent)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
    subresourceOperations: [
    'api_item_in_version_groups_in_shops_get_subresource' => [
        'pagination_client_enabled' => true,
    ],
    'api_item_in_version_groups_pokemon_holds_in_wilds_get_subresource' => [
        'pagination_client_enabled' => true,
    ],
],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'pocket' => 'exact',
    'slug' => 'exact',
    'versionGroup' => 'exact',
    'category.slug' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
])]
class ItemInVersionGroup extends AbstractDexEntity implements
    EntityGroupedByVersionGroupInterface,
    EntityHasDescriptionInterface,
    EntityHasFlavorTextInterface,
    EntityHasIconInterface,
    EntityHasNameInterface,
    EntityHasParentInterface,
    EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasFlavorTextTrait;
    use EntityHasDescriptionTrait;
    use EntityHasParentTrait;
    use EntityHasIconTrait;

    /**
     * @var Item
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="children")
     */
    protected EntityHasChildrenInterface $parent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemCategory", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"item_view", "capture_rate"})
     */
    private ItemCategory $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemPocketInVersionGroup", fetch="EAGER")
     * @Groups({"item_view"})
     */
    private ItemPocketInVersionGroup $pocket;

    /**
     * Cost of the item when bought.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $buy;

    /**
     * Cost of the item when sold.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $sell;

    /**
     * Effect of the move Fling when used with this item.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemFlingEffect", fetch="EAGER")
     * @Groups({"item_view"})
     */
    private ?ItemFlingEffect $flingEffect = null;

    /**
     * Power of the move Fling when used with this item.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"item_view"})
     */
    private ?int $flingPower;

    /**
     * Item attributes
     *
     * @var Collection<ItemFlag>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ItemFlag", fetch="EAGER")
     * @Groups({"item_view"})
     */
    private Collection $flags;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Berry", inversedBy="item", cascade={"all"}, orphanRemoval=true,
     *     fetch="EAGER")
     * @Groups({"item_view"})
     */
    private ?Berry $berry = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Machine", inversedBy="item", cascade={"all"}, orphanRemoval=true,
     *     fetch="EAGER")
     * @Groups({"item_view"})
     */
    private ?Machine $machine = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Decoration", inversedBy="item", cascade={"all"}, orphanRemoval=true,
     *     fetch="EAGER")
     * @Groups({"item_view"})
     */
    private ?Decoration $decoration = null;

    /**
     * @var Collection<PokemonWildHeldItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonWildHeldItem", mappedBy="item", fetch="EXTRA_LAZY")
     */
    #[ApiSubresource(maxDepth: 1)]
    private Collection $pokemonHoldsInWild;

    /**
     * @var Collection<ShopItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ShopItem", mappedBy="item", fetch="EXTRA_LAZY")
     */
    #[ApiSubresource(maxDepth: 1)]
    private Collection $inShops;

    /**
     * ItemInVersionGroup constructor.
     */
    public function __construct()
    {
        $this->flags = new ArrayCollection();
        $this->pokemonHoldsInWild = new ArrayCollection();
    }

    public function getCategory(): ?ItemCategory
    {
        return $this->category;
    }

    public function setCategory(ItemCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPocket(): ?ItemPocketInVersionGroup
    {
        return $this->pocket;
    }

    public function setPocket(ItemPocketInVersionGroup $pocket): self
    {
        $this->pocket = $pocket;

        return $this;
    }

    public function getBuy(): ?int
    {
        return $this->buy;
    }

    public function setBuy(?int $buy): self
    {
        $this->buy = $buy;

        return $this;
    }

    public function getSell(): ?int
    {
        return $this->sell;
    }

    public function setSell(?int $sell): self
    {
        $this->sell = $sell;

        return $this;
    }

    public function getFlingEffect(): ?ItemFlingEffect
    {
        return $this->flingEffect;
    }

    public function setFlingEffect(?ItemFlingEffect $flingEffect): self
    {
        $this->flingEffect = $flingEffect;

        return $this;
    }

    public function getFlingPower(): ?int
    {
        return $this->flingPower;
    }

    public function setFlingPower(?int $flingPower): self
    {
        $this->flingPower = $flingPower;

        return $this;
    }

    /**
     * @return Collection<ItemFlag>
     */
    public function getFlags(): Collection
    {
        return $this->flags;
    }

    public function addFlag(ItemFlag $flag): self
    {
        if (!$this->flags->contains($flag)) {
            $this->flags->add($flag);
        }

        return $this;
    }

    public function removeFlag(ItemFlag $flag): self
    {
        if ($this->flags->contains($flag)) {
            $this->flags->removeElement($flag);
        }

        return $this;
    }

    public function getBerry(): ?Berry
    {
        return $this->berry;
    }

    public function setBerry(?Berry $berry): self
    {
        $this->berry = $berry;

        return $this;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): self
    {
        $this->machine = $machine;

        return $this;
    }

    public function getDecoration(): ?Decoration
    {
        return $this->decoration;
    }

    public function setDecoration(?Decoration $decoration): ItemInVersionGroup
    {
        $this->decoration = $decoration;

        return $this;
    }

    /**
     * @return Collection<PokemonWildHeldItem>
     */
    public function getPokemonHoldsInWild(): Collection
    {
        return $this->pokemonHoldsInWild;
    }

    /**
     * @return Collection<ShopItem>
     */
    public function getInShops(): Collection
    {
        return $this->inShops;
    }
}
