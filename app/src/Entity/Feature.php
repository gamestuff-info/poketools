<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A feature in a version group
 *
 * The presence (or lack thereof) of features in a version group will enable
 * or disable certain site functionality on that version group's pages.
 *
 * @ORM\Entity(repositoryClass="App\Repository\FeatureRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
)]
class Feature extends AbstractDexEntity implements EntityHasSlugInterface, EntityHasDescriptionInterface
{
    use EntityHasDescriptionTrait;

    /**
     * @ORM\Column(type="string")
     * @Groups({"read"})
     */
    private ?string $slug;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): Feature
    {
        $this->slug = $slug;

        return $this;
    }
}
