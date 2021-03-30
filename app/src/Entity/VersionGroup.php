<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ds\Set;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A set of games that are part of the same release, differing only in trivial
 * ways (e.g. Pokemon available).
 *
 * @ORM\Entity(repositoryClass="App\Repository\VersionGroupRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
)]
class VersionGroup extends AbstractDexEntity implements GroupableInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByGenerationInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByGenerationTrait;
    use EntityIsSortableTrait;

    /**
     * The generation this version group belongs to
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Generation", inversedBy="versionGroups")
     * @Assert\NotNull()
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    protected Generation $generation;

    /**
     * Versions in this version group
     *
     * @var Collection<Version>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Version", mappedBy="versionGroup")
     * @ORM\OrderBy({"position": "ASC"})
     */
    private Collection $versions;

    /**
     * A list of features in this version group
     *
     * @var Collection<Feature>
     * @ORM\ManyToMany(targetEntity="App\Entity\Feature", fetch="EAGER")
     * @Groups({"read"})
     */
    #[ApiProperty(iri: 'https://schema.org/featureList')]
    private Collection $features;

    /**
     * Cache feature strings for faster checking
     */
    private ?Set $featureStrings = null;

    public function __construct()
    {
        $this->versions = new ArrayCollection();
        $this->features = new ArrayCollection();
    }

    /**
     * @return Collection<Version>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    /**
     * @param iterable<Version> $versions
     *
     * @return self
     */
    public function addVersions(iterable $versions): self
    {
        foreach ($versions as $version) {
            $this->addVersion($version);
        }

        return $this;
    }

    public function addVersion(Version $version): self
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setVersionGroup($this);
        }

        return $this;
    }

    /**
     * @param iterable<Version> $versions
     *
     * @return self
     */
    public function removeVersions(iterable $versions): self
    {
        foreach ($versions as $version) {
            $this->removeVersion($version);
        }

        return $this;
    }

    public function removeVersion(Version $version): self
    {
        if ($this->versions->contains($version)) {
            $this->versions->removeElement($version);
            $version->setVersionGroup(null);
        }

        return $this;
    }

    public function addFeature(Feature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            if ($this->featureStrings !== null) {
                $this->featureStrings->add($feature->getSlug());
            }
        }

        return $this;
    }

    public function removeFeature(Feature $feature): self
    {
        if ($this->features->contains($feature)) {
            $this->features->removeElement($feature);
            if ($this->featureStrings !== null) {
                $this->featureStrings->remove($feature->getSlug());
            }
        }

        return $this;
    }

    /**
     * @return Collection<Feature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function hasFeatureString(string $feature): bool
    {
        if ($this->featureStrings === null) {
            $this->featureStrings = new Set($this->features->map(fn(Feature $feature) => $feature->getSlug()));
        }

        return $this->featureStrings->contains($feature);
    }
}
