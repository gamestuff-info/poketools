<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Embeddable
 */
class PokemonStat
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="255")
     * @Groups({"read"})
     */
    private ?int $baseValue = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="-255", max="255")
     * @Groups({"read"})
     */
    private ?int $effortChange = null;

    public function getBaseValue(): ?int
    {
        return $this->baseValue;
    }

    public function setBaseValue(int $baseValue): self
    {
        $this->baseValue = $baseValue;

        return $this;
    }

    public function getEffortChange(): ?int
    {
        return $this->effortChange;
    }

    public function setEffortChange(int $effortChange): self
    {
        $this->effortChange = $effortChange;

        return $this;
    }
}
