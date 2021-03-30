<?php


namespace App\Entity;

/**
 * Entity has a description.
 */
interface EntityHasDescriptionInterface
{

    public function getShortDescription(): ?string;

    public function setShortDescription(?string $shortDescription);

    public function getDescription(): ?string;

    public function setDescription(?string $description);
}
