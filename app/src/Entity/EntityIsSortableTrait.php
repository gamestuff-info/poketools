<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Default implementation of App\Entity\EntityIsSortableInterface
 */
trait EntityIsSortableTrait
{

    /**
     * Sort position
     *
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    protected int $position = 0;

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
