<?php


namespace App\Entity;

/**
 * Interface for entities that can be used as groups for other entities.
 */
interface GroupableInterface
{
    public function getId(): int;

    public function getSlug(): ?string;
}
