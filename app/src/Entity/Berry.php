<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Embeddable\Range;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Time;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Berry, consumable item that grows on trees.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BerryRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
)]
class Berry extends AbstractDexEntity implements EntityHasFlavorTextInterface
{
    use EntityHasFlavorTextTrait;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemInVersionGroup", mappedBy="berry")
     */
    private ItemInVersionGroup $item;

    /**
     * Game's berry number
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private ?int $number;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BerryFirmness")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private BerryFirmness $firmness;

    /**
     * Natural Gift’s power when used with this Berry
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private ?int $naturalGiftPower;

    /**
     * The Type that Natural Gift has when used with this Berry
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @Groups({"read"})
     */
    private ?Type $naturalGiftType;

    /**
     * The size of the berry
     *
     * @ORM\Column(type="safe_object")
     * @Assert\NotBlank()
     */
    private Length $size;

    /**
     * The number of berries that can grow on one tree
     *
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private Range $harvest;

    /**
     * Time it takes the tree to grow one stage.
     *
     * Berry trees go through several of these growth stages before they can be
     * picked.
     *
     * @ORM\Column(type="safe_object")
     * @Assert\NotBlank()
     */
    private Time $growthTime;

    /**
     * The speed at which this Berry dries out the soil as it grows. A higher
     * rate means the soil dries more quickly.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $water;

    /**
     * How susceptible this Berry is to weeds.  A higher value means weeding
     * the plant increases the yield more.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $weeds;

    /**
     * How susceptible this Berry is to pests.  A higher value means removing
     * pests near the plant increases the yield more.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $pests;

    /**
     * The smoothness of this Berry, used in making Pokéblocks or Poffins.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $smoothness;

    /**
     * @var Collection<BerryFlavorLevel>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\BerryFlavorLevel", mappedBy="berry", cascade={"all"},
     *     orphanRemoval=true, fetch="EAGER")
     * @Groups({"read"})
     */
    private Collection $flavors;

    public function __construct()
    {
        $this->flavors = new ArrayCollection();
    }

    public function getItem(): ?ItemInVersionGroup
    {
        return $this->item;
    }

    public function setItem(ItemInVersionGroup $item): self
    {
        $this->item = $item;
        $item->setBerry($this);

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): Berry
    {
        $this->number = $number;

        return $this;
    }

    public function getFirmness(): ?BerryFirmness
    {
        return $this->firmness;
    }

    public function setFirmness(BerryFirmness $firmness): self
    {
        $this->firmness = $firmness;

        return $this;
    }

    public function getNaturalGiftPower(): ?int
    {
        return $this->naturalGiftPower;
    }

    public function setNaturalGiftPower(?int $naturalGiftPower): self
    {
        $this->naturalGiftPower = $naturalGiftPower;

        return $this;
    }

    public function getNaturalGiftType(): ?Type
    {
        return $this->naturalGiftType;
    }

    public function setNaturalGiftType(?Type $naturalGiftType): self
    {
        $this->naturalGiftType = $naturalGiftType;

        return $this;
    }

    public function getSize(): ?Length
    {
        return $this->size;
    }

    /**
     * @Groups({"read"})
     */
    public function getSizeMillimeters(): float
    {
        return $this->size->toUnit('mm');
    }

    public function setSize(Length $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getHarvest(): ?Range
    {
        return $this->harvest;
    }

    public function setHarvest(Range $harvest): self
    {
        $this->harvest = $harvest;

        return $this;
    }

    public function getGrowthTime(): ?Time
    {
        return $this->growthTime;
    }

    /**
     * @Groups({"read"})
     */
    public function getGrowthTimeSeconds(): float
    {
        return $this->growthTime->toUnit('s');
    }

    public function setGrowthTime(Time $growthTime): self
    {
        $this->growthTime = $growthTime;

        return $this;
    }

    public function getWater(): ?int
    {
        return $this->water;
    }

    public function setWater(?int $water): self
    {
        $this->water = $water;

        return $this;
    }

    public function getWeeds(): ?int
    {
        return $this->weeds;
    }

    public function setWeeds(?int $weeds): self
    {
        $this->weeds = $weeds;

        return $this;
    }

    public function getPests(): ?int
    {
        return $this->pests;
    }

    public function setPests(?int $pests): self
    {
        $this->pests = $pests;

        return $this;
    }

    public function getSmoothness(): ?int
    {
        return $this->smoothness;
    }

    public function setSmoothness(?int $smoothness): self
    {
        $this->smoothness = $smoothness;

        return $this;
    }

    /**
     * @return Collection<BerryFlavorLevel>
     */
    public function getFlavors(): Collection
    {
        return $this->flavors;
    }

    public function addFlavor(BerryFlavorLevel $flavorLevel): self
    {
        if (!$this->flavors->contains($flavorLevel)) {
            $this->flavors->add($flavorLevel);
            $flavorLevel->setBerry($this);
        }

        return $this;
    }

    public function removeFlavor(BerryFlavorLevel $flavorLevel): self
    {
        if ($this->flavors->contains($flavorLevel)) {
            $this->flavors->removeElement($flavorLevel);
            $flavorLevel->setBerry(null);
        }

        return $this;
    }
}
