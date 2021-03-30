<?php


namespace App\Entity;

/**
 * Entities with a given sort order.
 */
interface EntityIsSortableInterface
{
    public function getPosition();

    public function setPosition(int $position);
}
