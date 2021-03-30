<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An item category. Not official.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemCategoryRepository")
 * @Gedmo\Tree(type="materializedPath")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
)]
class ItemCategory extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

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
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\TreePath()
     */
    private ?string $treePath;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemCategory", inversedBy="treeChildren", cascade={"remove"})
     * @Gedmo\TreeParent()
     */
    private ?ItemCategory $treeParent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\TreeLevel()
     */
    private ?int $treeLevel;

    /**
     * @var Collection<ItemCategory>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ItemCategory", mappedBy="treeParent")
     */
    private Collection $treeChildren;

    public function __construct()
    {
        $this->treeChildren = new ArrayCollection();
    }

    public function getTreeParent(): ?ItemCategory
    {
        return $this->treeParent;
    }

    public function setTreeParent(?ItemCategory $treeParent): self
    {
        $this->treeParent = $treeParent;

        return $this;
    }

    public function getFullTree(): array
    {
        return $this->calcFullTree();
    }

    /**
     * @param array $tree
     *
     * @return ItemCategory[]
     */
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

    private function getTreeRoot(): ItemCategory
    {
        if (isset($this->treeParent)) {
            return $this->treeParent->getTreeRoot();
        }

        return $this;
    }

    /**
     * @return Collection<ItemCategory>
     */
    public function getTreeChildren()
    {
        return $this->treeChildren;
    }
}
