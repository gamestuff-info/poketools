<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A place in the Pokémon world.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationInVersionGroupRepository")
 * @Gedmo\Tree(type="materializedPath")
 *
 * @method Location getParent()
 * @method self setParent(Location $parent)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
    paginationClientEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'region' => 'exact',
    'slug' => 'exact',
    'versionGroup' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
    'region.position',
])]
class LocationInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByVersionGroupInterface, EntityHasDescriptionInterface
{

    use EntityHasParentTrait;
    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasDescriptionTrait;

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
     * @var Location
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="children")
     */
    protected EntityHasChildrenInterface $parent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RegionInVersionGroup")
     * @Assert\NotNull
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    private RegionInVersionGroup $region;

    /**
     * @var Collection<LocationArea>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationArea", mappedBy="location", cascade={"ALL"}, orphanRemoval=true,
     *     fetch="EAGER")
     * @ORM\OrderBy({"position": "ASC"})
     * @Assert\NotBlank
     * @Groups({"location_view"})
     */
    private Collection $areas;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\LocationMap", mappedBy="location", cascade={"ALL"}, orphanRemoval=true)
     * @Groups({"location_index", "location_view", "pokemon_view"})
     */
    private ?LocationMap $map = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\TreePath()
     */
    private ?string $treePath;

    /**
     * The location that contains this one (e.g. The Tin Tower is inside Ecruteak city,
     * this would be the relevant Ecruteak city location entity).
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationInVersionGroup", inversedBy="subLocations")
     * @Gedmo\TreeParent()
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    private ?LocationInVersionGroup $superLocation = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\TreeLevel()
     */
    private ?int $treeLevel;

    /**
     * @var Collection<LocationInVersionGroup>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationInVersionGroup", mappedBy="superLocation")
     * @ORM\OrderBy({"name": "ASC"})
     * @Groups({"read"})
     */
    private Collection $subLocations;

    public function __construct()
    {
        $this->areas = new ArrayCollection();
        $this->subLocations = new ArrayCollection();
    }

    public function getRegion(): ?RegionInVersionGroup
    {
        return $this->region;
    }

    public function setRegion(RegionInVersionGroup $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection<LocationArea>
     */
    public function getAreas(): Collection
    {
        return $this->areas;
    }

    public function addAreas($areas): self
    {
        foreach ($areas as $area) {
            $this->addArea($area);
        }

        return $this;
    }

    public function addArea(LocationArea $area): self
    {
        if (!$this->areas->contains($area)) {
            $this->areas->add($area);
            $area->setLocation($this);
        }

        return $this;
    }

    public function removeAreas($areas): self
    {
        foreach ($areas as $area) {
            $this->removeArea($area);
        }

        return $this;
    }

    public function removeArea(LocationArea $child): self
    {
        if ($this->areas->contains($child)) {
            $this->areas->removeElement($child);
            $child->setLocation(null);
        }

        return $this;
    }

    public function getMap(): ?LocationMap
    {
        return $this->map;
    }

    /**
     * @return LocationMap|null
     * @Groups({"location_index", "location_view", "pokemon_view"})
     */
    public function getEffectiveMap(): ?LocationMap
    {
        if ($this->map) {
            return $this->map;
        } elseif ($this->superLocation) {
            return $this->superLocation->getEffectiveMap();
        }

        return null;
    }

    public function setMap(?LocationMap $map): self
    {
        $this->map = $map;
        if ($map !== null) {
            $map->setLocation($this);
        }

        return $this;
    }

    public function getTreePath(): ?string
    {
        return $this->treePath;
    }

    public function setTreePath(?string $treePath): self
    {
        $this->treePath = $treePath;

        return $this;
    }

    public function getSuperLocation(): ?LocationInVersionGroup
    {
        return $this->superLocation;
    }

    public function setSuperLocation(?LocationInVersionGroup $superLocation): self
    {
        $this->superLocation = $superLocation;

        return $this;
    }

    public function getTreeLevel(): ?int
    {
        return $this->treeLevel;
    }

    public function addSubLocation(LocationInVersionGroup $subLocation): self
    {
        if (!$this->subLocations->contains($subLocation)) {
            $this->subLocations->add($subLocation);
            $subLocation->setSuperLocation($this);
        }

        return $this;
    }

    public function removeSubLocation(LocationInVersionGroup $subLocation): self
    {
        if ($this->subLocations->contains($subLocation)) {
            $this->subLocations->removeElement($subLocation);
            $subLocation->setSuperLocation(null);
        }

        return $this;
    }

    /**
     * @return LocationInVersionGroup[]
     */
    public function getFullTree(): array
    {
        return $this->calcFullTree();
    }

    private function calcFullTree(array &$tree = []): array
    {
        if (empty($tree)) {
            $root = $this->getTreeRoot();
            $tree[] = $root;
            foreach ($root->getSubLocations() as $child) {
                $child->calcFullTree($tree);
            }
        } else {
            $tree[] = $this;

            foreach ($this->subLocations as $child) {
                $child->calcFullTree($tree);
            }
        }

        return $tree;
    }

    private function getTreeRoot(): LocationInVersionGroup
    {
        if (isset($this->superLocation)) {
            return $this->superLocation->getTreeRoot();
        }

        return $this;
    }

    /**
     * @return Collection<LocationInVersionGroup
     */
    public function getSubLocations(): Collection
    {
        return $this->subLocations;
    }
}
