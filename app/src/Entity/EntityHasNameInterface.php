<?php


namespace App\Entity;

/**
 * Entities that have names.
 */
interface EntityHasNameInterface
{
    public function getName(): ?string;

    public function setName(string $name);
}
