<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Decoration for the home or secret base
 *
 * @ORM\Entity(repositoryClass="App\Repository\DecorationRepository")
 */
class Decoration extends AbstractDexEntity
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemInVersionGroup", mappedBy="decoration")
     */
    private ItemInVersionGroup $item;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private int $width;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private int $height;

    public function getItem(): ItemInVersionGroup
    {
        return $this->item;
    }

    public function setItem(ItemInVersionGroup $item): Decoration
    {
        $this->item = $item;
        $item->setDecoration($this);

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): Decoration
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): Decoration
    {
        $this->height = $height;

        return $this;
    }
}
