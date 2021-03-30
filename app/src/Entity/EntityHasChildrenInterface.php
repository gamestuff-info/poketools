<?php


namespace App\Entity;


use Doctrine\Common\Collections\Collection;

/**
 * Entities with children.
 *
 * This is the counterpart to App\Entity\EntityHasParentInterface.
 */
interface EntityHasChildrenInterface
{
    /**
     * @return Collection<EntityHasParentInterface>
     */
    public function getChildren(): Collection;

    public function addChild(EntityHasParentInterface $child);

    public function removeChild(EntityHasParentInterface $child);

    public function findChildByGrouping(GroupableInterface $group): ?EntityGroupedInterface;
}
