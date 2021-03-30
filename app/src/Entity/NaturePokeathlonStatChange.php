<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Specifies how a Nature affects a Pokéathlon stat.
 *
 * @ORM\Entity(repositoryClass="App\Repository\NaturePokeathlonStatChangeRepository")
 */
class NaturePokeathlonStatChange
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Nature", inversedBy="pokeathlonStatChanges")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    private Nature $nature;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PokeathlonStat")
     * @ORM\Id()
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private PokeathlonStat $pokeathlonStat;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="-5", max="5")
     * @Groups({"read"})
     */
    private int $maxChange;

    public function getNature(): ?Nature
    {
        return $this->nature;
    }

    public function setNature(Nature $nature): self
    {
        $this->nature = $nature;

        return $this;
    }

    public function getPokeathlonStat(): ?PokeathlonStat
    {
        return $this->pokeathlonStat;
    }

    public function setPokeathlonStat(PokeathlonStat $pokeathlonStat): self
    {
        $this->pokeathlonStat = $pokeathlonStat;

        return $this;
    }

    public function getMaxChange(): ?int
    {
        return $this->maxChange;
    }

    public function setMaxChange(int $maxChange): self
    {
        $this->maxChange = $maxChange;

        return $this;
    }
}
