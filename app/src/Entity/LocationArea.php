<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A sub-area of a location. (e.g. 1F, Basement, etc.)
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationAreaRepository")
 * @Gedmo\Tree(type="materializedPath")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
)]
#[ApiFilter(GroupFilter::class)]
class LocationArea extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;
    use EntityIsSortableTrait;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationInVersionGroup", inversedBy="areas")
     * @Assert\NotNull
     * @Groups({"item_view", "pokemon_view"})
     */
    private ?LocationInVersionGroup $location;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\TreePath()
     */
    private string $treePath;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationArea", inversedBy="treeChildren", cascade={"remove"})
     * @Gedmo\TreeParent()
     */
    private LocationArea $treeParent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\TreeLevel()
     */
    private int $treeLevel;

    /**
     * @var Collection<LocationArea>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationArea", mappedBy="treeParent", cascade={"ALL"})
     * @ORM\OrderBy({"position": "ASC"})
     * @Groups({"location_view"})
     */
    private Collection $treeChildren;

    /**
     * @var Collection<Shop>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Shop", mappedBy="locationArea", cascade={"ALL"})
     * @ORM\OrderBy({"isDefault": "ASC", "name": "ASC"})
     * @Groups({"location_view"})
     */
    private Collection $shops;

    /**
     * @var Collection<Encounter>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Encounter", mappedBy="locationArea")
     * @ORM\OrderBy({"position": "ASC"})
     */
    #[ApiSubresource(maxDepth: 1)]
    private Collection $encounters;

    public function __construct()
    {
        $this->treeChildren = new ArrayCollection();
        $this->shops = new ArrayCollection();
    }

    public static function getGroupField(): string
    {
        return 'location';
    }

    public function getLocation(): ?LocationInVersionGroup
    {
        return $this->location;
    }

    public function setLocation(?LocationInVersionGroup $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getTreeParent(): ?LocationArea
    {
        return $this->treeParent;
    }

    public function setTreeParent(?LocationArea $treeParent): self
    {
        $this->treeParent = $treeParent;

        return $this;
    }

    public function getFullTree(): array
    {
        return $this->calcFullTree();
    }

    private function calcFullTree(array &$tree = []): array
    {
        if (empty($tree)) {
            $root = $this->getTreeRoot();
            $tree[] = $root;
            foreach ($root->getTreeChildren() as $child) {
                $child->calcFullTree($tree);
            }
        } else {
            $tree[] = $this;

            foreach ($this->treeChildren as $child) {
                $child->calcFullTree($tree);
            }
        }

        return $tree;
    }

    /**
     * @Groups({"item_view"})
     */
    public function getTreeRoot(): LocationArea
    {
        if (isset($this->treeParent)) {
            return $this->treeParent->getTreeRoot();
        }

        return $this;
    }

    /**
     * @return Collection<LocationArea>
     */
    public function getTreeChildren(): Collection
    {
        return $this->treeChildren;
    }

    public function addTreeChild(LocationArea $child): self
    {
        if (!$this->treeChildren->contains($child)) {
            $this->treeChildren->add($child);
            $child->setTreeParent($this);
        }

        return $this;
    }

    public function removeTreeChild(LocationArea $child): self
    {
        if ($this->treeChildren->contains($child)) {
            $this->treeChildren->removeElement($child);
            $child->setTreeParent(null);
        }

        return $this;
    }

    public function getTreePath(): string
    {
        return $this->treePath;
    }

    public function getTreeParents(bool $includeCurrent = false): array
    {
        $parents = $this->calcTreeParents();
        if ($includeCurrent) {
            $parents[] = $this;
        }

        return $parents;
    }

    private function calcTreeParents(array &$parents = []): array
    {
        if (empty($parents)) {
            $root = $this->getTreeRoot();
            $parents[] = $root;
            foreach ($root->getTreeChildren() as $child) {
                $child->calcTreeParents($parents);
            }
        } else {
            foreach ($this->treeChildren as $child) {
                $child->calcTreeParents($parents);
            }
        }

        return $parents;
    }

    /**
     * @return Collection<Shop>
     */
    public function getShops(): Collection
    {
        return $this->shops;
    }

    public function addShop(Shop $shop): self
    {
        if (!$this->shops->contains($shop)) {
            $this->shops->add($shop);
            $shop->setLocationArea($this);
        }

        return $this;
    }

    public function removeShop(Shop $shop): self
    {
        if ($this->shops->contains($shop)) {
            $this->shops->removeElement($shop);
            $shop->setLocationArea(null);
        }

        return $this;
    }
}
