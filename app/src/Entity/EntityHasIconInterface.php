<?php


namespace App\Entity;

/**
 * Entities with icons
 */
interface EntityHasIconInterface
{
    public function getIcon(): ?string;

    public function setIcon(?string $image);
}
