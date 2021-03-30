<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Specifies how likely a Pokémon with a specific Nature is to use a move of a
 * particular battle style in Battle Palace or Battle Tent.
 *
 * @ORM\Entity(repositoryClass="App\Repository\NatureBattleStylePreferenceRepository")
 */
class NatureBattleStylePreference
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Nature", inversedBy="battleStylePreferences")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    private Nature $nature;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BattleStyle")
     * @ORM\Id()
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private BattleStyle $battleStyle;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="100")
     * @Groups({"read"})
     */
    private int $lowHpChance;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="100")
     * @Groups({"read"})
     */
    private int $highHpChance;

    public function getNature(): ?Nature
    {
        return $this->nature;
    }

    public function setNature(?Nature $nature): self
    {
        $this->nature = $nature;

        return $this;
    }

    public function getBattleStyle(): ?BattleStyle
    {
        return $this->battleStyle;
    }

    public function setBattleStyle(BattleStyle $battleStyle): self
    {
        $this->battleStyle = $battleStyle;

        return $this;
    }

    public function getLowHpChance(): ?int
    {
        return $this->lowHpChance;
    }

    public function setLowHpChance(int $lowHpChance): self
    {
        $this->lowHpChance = $lowHpChance;

        return $this;
    }

    public function getHighHpChance(): ?int
    {
        return $this->highHpChance;
    }

    public function setHighHpChance(int $highHpChance): self
    {
        $this->highHpChance = $highHpChance;

        return $this;
    }
}
