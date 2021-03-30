<?php


namespace App\Entity;

/**
 * Entity could be the default in a group of related entities.
 */
interface EntityHasDefaultInterface
{
    public function isDefault(): bool;

    public function setDefault(bool $default);
}
