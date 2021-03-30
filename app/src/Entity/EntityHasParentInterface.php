<?php


namespace App\Entity;

/**
 * Entities with parents
 *
 * This is mainly useful for grouping grouped objects together, e.g. relating a
 * single ability through all version groups.
 */
interface EntityHasParentInterface
{
    public function getParent(): ?EntityHasChildrenInterface;

    public function setParent(EntityHasChildrenInterface $parent);
}
