<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Berry flavor level.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BerryFlavorLevelRepository")
 */
class BerryFlavorLevel
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Berry", inversedBy="flavors")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    private Berry $berry;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BerryFlavor")
     * @ORM\Id()
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private BerryFlavor $flavor;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({"read"})
     */
    private int $level;

    public function getBerry(): ?Berry
    {
        return $this->berry;
    }

    public function setBerry(Berry $berry): self
    {
        $this->berry = $berry;

        return $this;
    }

    public function getFlavor(): ?BerryFlavor
    {
        return $this->flavor;
    }

    public function setFlavor(BerryFlavor $flavor): self
    {
        $this->flavor = $flavor;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }
}
