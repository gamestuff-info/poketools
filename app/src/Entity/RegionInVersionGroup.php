<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use App\Entity\Media\RegionMap;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Major areas of the world: Kanto, Johto, etc.
 *
 * @ORM\Entity(repositoryClass="App\Repository\RegionInVersionGroupRepository")
 *
 * @method Region getParent()
 * @method self setParent(Region $parent)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
    paginationClientEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'slug' => 'exact',
    'versionGroup' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
class RegionInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{
    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="children")
     * @Assert\NotNull()
     */
    protected EntityHasChildrenInterface $parent;

    /**
     * @var Collection<RegionMap>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Media\RegionMap", mappedBy="region", cascade={"ALL"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     * @Groups({"location_index"})
     */
    private Collection $maps;

    public function __construct()
    {
        $this->maps = new ArrayCollection();
    }

    /**
     * @return Collection<RegionMap>
     */
    public function getMaps(): Collection
    {
        return $this->maps;
    }

    public function addMap(RegionMap $map): self
    {
        if (!$this->maps->contains($map)) {
            $this->maps->add($map);
            $map->setRegion($this);
        }

        return $this;
    }

    public function removeMap(RegionMap $map): self
    {
        if ($this->maps->contains($map)) {
            $this->maps->removeElement($map);
            $map->setRegion(null);
        }

        return $this;
    }
}
