<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Media\RegionMap;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationMapRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']]
)]
class LocationMap
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\LocationInVersionGroup", inversedBy="map")
     * @ORM\Id()
     * @Assert\NotNull()
     */
    private LocationInVersionGroup $location;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\RegionMap")
     * @Assert\NotNull()
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    private RegionMap $map;

    /**
     * SVG map overlay highlighting this location
     *
     * @ORM\Column(type="text")
     * @Groups({"read"})
     */
    private ?string $overlay;

    /**
     * The order in which the overlays are drawn on top of each other.
     *
     * Higher numbers are on top of lower numbers.  The stacking relationship
     * between overlays with the same z-index is undefined.
     *
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Groups({"read"})
     */
    private int $zIndex = 0;

    public function getLocation(): ?LocationInVersionGroup
    {
        return $this->location;
    }

    public function setLocation(LocationInVersionGroup $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getMap(): ?RegionMap
    {
        return $this->map;
    }

    public function setMap(RegionMap $map): self
    {
        $this->map = $map;

        return $this;
    }

    public function getOverlay(): ?string
    {
        return $this->overlay;
    }

    public function setOverlay(?string $overlay): self
    {
        $this->overlay = $overlay;

        return $this;
    }

    public function getZIndex(): int
    {
        return $this->zIndex;
    }

    public function setZIndex(int $zIndex): self
    {
        $this->zIndex = $zIndex;

        return $this;
    }
}
