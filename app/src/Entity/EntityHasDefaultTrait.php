<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Default implementation of EntityHasDefaultInterface
 */
trait EntityHasDefaultTrait
{

    /**
     * Is this the default entity?
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    protected bool $isDefault = false;

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }
}
