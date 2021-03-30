<?php


namespace App\Entity;

/**
 * Entity has in-game flavor text.
 */
interface EntityHasFlavorTextInterface
{
    public function getFlavorText(): ?string;

    public function setFlavorText(?string $flavorText);
}
