<?php

namespace App\Entity\Media;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\AbstractDexEntity;
use App\Entity\EntityHasNameAndSlugTrait;
use App\Entity\EntityHasNameInterface;
use App\Entity\EntityHasSlugInterface;
use App\Entity\EntityIsSortableInterface;
use App\Entity\EntityIsSortableTrait;
use App\Entity\RegionInVersionGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Region Map
 *
 * @ORM\Entity(repositoryClass="App\Repository\Media\RegionMapRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'region' => 'exact',
])]
class RegionMap extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{
    use MediaEntityTrait, EntityHasNameAndSlugTrait {
        MediaEntityTrait::__toString insteadof EntityHasNameAndSlugTrait;
    }
    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RegionInVersionGroup", inversedBy="maps")
     * @Assert\NotBlank()
     */
    private RegionInVersionGroup $region;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private int $width;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private int $height;

    public function __construct(?string $url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }

    public function getRegion(): ?RegionInVersionGroup
    {
        return $this->region;
    }

    public function setRegion(RegionInVersionGroup $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }
}
