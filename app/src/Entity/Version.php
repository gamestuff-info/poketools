<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A Pokemon game version.
 *
 * @ORM\Entity(repositoryClass="App\Repository\VersionRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
    paginationClientEnabled: true,
)]
class Version extends AbstractDexEntity implements GroupableInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByVersionGroupInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityIsSortableTrait;

    /**
     * The Version group this Version belongs to
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VersionGroup", inversedBy="versions", fetch="EAGER")
     * @Assert\NotNull()
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    protected VersionGroup $versionGroup;

    /**
     * Get this version's feature slugs
     *
     * @return Collection<string>
     * @Groups({"read"})
     */
    public function getFeatureSlugs(): Collection
    {
        return $this->versionGroup->getFeatures()->map(fn(Feature $feature) => $feature->getSlug());
    }

    /**
     * @Groups({"read"})
     */
    public function getGenerationNumber(): int {
        return $this->versionGroup->getGeneration()->getNumber();
    }
}
