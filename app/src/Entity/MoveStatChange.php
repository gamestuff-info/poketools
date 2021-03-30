<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stat changes moves (may) make.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveStatChangeRepository")
 */
class MoveStatChange
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveInVersionGroup", inversedBy="statChanges")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    private MoveInVersionGroup $move;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat")
     * @ORM\Id()
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private Stat $stat;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="-10", max="10")
     * @Groups({"read"})
     */
    private int $change;

    public function getMove(): MoveInVersionGroup
    {
        return $this->move;
    }

    public function setMove(MoveInVersionGroup $move): self
    {
        $this->move = $move;

        return $this;
    }

    public function getStat(): Stat
    {
        return $this->stat;
    }

    public function setStat(Stat $stat): self
    {
        $this->stat = $stat;

        return $this;
    }

    public function getChange(): int
    {
        return $this->change;
    }

    public function setChange(int $change): self
    {
        $this->change = $change;

        return $this;
    }
}
