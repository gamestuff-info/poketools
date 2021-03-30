<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Flavor text hinting at which stat contains a Pokémon's highest IV.
 *
 * @ORM\Entity(repositoryClass="App\Repository\CharacteristicRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    paginationClientEnabled: true,
)]
class Characteristic extends AbstractDexEntity implements EntityHasFlavorTextInterface
{

    use EntityHasFlavorTextTrait;

    /**
     * The Pokémon's highest stat IV
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat")
     * @Assert\NotNull()
     * @Groups({"read"})
     */
    private Stat $stat;

    /**
     * The highest IV mod 5
     *
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({"read"})
     */
    private ?int $ivDeterminator;

    public function getStat(): ?Stat
    {
        return $this->stat;
    }

    public function setStat(Stat $stat): self
    {
        $this->stat = $stat;

        return $this;
    }

    public function getIvDeterminator(): ?int
    {
        return $this->ivDeterminator;
    }

    public function setIvDeterminator(int $ivDeterminator): self
    {
        $this->ivDeterminator = $ivDeterminator;

        return $this;
    }
}
