<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Default implementation of App\Entity\EntityHasFlavorTextInterface
 */
trait EntityHasFlavorTextTrait
{

    /**
     * In-game flavor text describing this entity
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read"})
     */
    protected ?string $flavorText;

    public function getFlavorText(): ?string
    {
        return $this->flavorText;
    }

    public function setFlavorText(?string $flavorText): self
    {
        // Empty flavor text is the same as no flavor text
        if ($flavorText === '') {
            $this->flavorText = null;
        } else {
            $this->flavorText = $flavorText;
        }

        return $this;
    }
}
