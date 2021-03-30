<?php


namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Ds\Map;

/**
 * Default implementation of App\Entity\EntityHasChildrenInterface
 *
 * Classes using this trait will still need to define the $children property
 * and its mappings for the ORM.  This trait only implements the boilerplate
 * getter/setter methods.
 */
trait EntityHasChildrenTrait
{

    /**
     * @var Collection<EntityHasParentInterface>
     */
    protected Collection $children;

    /**
     * @todo This should be replaced when https://github.com/doctrine/orm/issues/6667 is fixed.
     */
    private ?Map $childMap = null;

    /**
     * @return Collection<EntityHasParentInterface>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param iterable<EntityHasParentInterface> $children
     *
     * @return self
     */
    public function addChildren($children)
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * @param EntityHasParentInterface $child
     *
     * @return self
     */
    public function addChild(EntityHasParentInterface $child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
            if ($this->childMap !== null && is_a($child, EntityGroupedInterface::class)) {
                $this->childMap[$child->getGroup()->getId()] = $child;
            }
        }

        return $this;
    }

    /**
     * @param iterable<EntityHasParentInterface> $children
     *
     * @return self
     */
    public function removeChildren($children)
    {
        foreach ($children as $child) {
            $this->removeChild($child);
        }

        return $this;
    }

    /**
     * @param EntityHasParentInterface $child
     *
     * @return self
     */
    public function removeChild(EntityHasParentInterface $child)
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
            if ($this->childMap !== null && is_a($child, EntityGroupedInterface::class)) {
                $this->childMap->remove($child->getGroup()->getId());
            }
        }

        return $this;
    }

    /**
     * Finds a child by grouping.
     *
     * @param GroupableInterface $group
     *
     * @return EntityGroupedInterface|null
     *  The entity, or null if it does not exist.
     */
    public function findChildByGrouping(GroupableInterface $group): ?EntityGroupedInterface
    {
        if ($this->childMap === null) {
            $this->childMap = new Map();
            $this->childMap->allocate($this->children->count());
            foreach ($this->children as $child) {
                $this->childMap[$child->getGroup()->getId()] = $child;
            }
        }

        return $this->childMap->hasKey($group->getId()) ? $this->childMap->get($group->getId()) : null;
    }
}
