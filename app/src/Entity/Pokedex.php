<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A collection of Pokémon species ordered in a particular way.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokedexRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']]
)]
class Pokedex extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityHasDefaultInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityHasDefaultTrait;

    /**
     * @var Collection<VersionGroup>
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\VersionGroup")
     * @Assert\NotBlank()
     */
    private Collection $versionGroups;

    public function __construct()
    {
        $this->versionGroups = new ArrayCollection();
    }

    /**
     * @return Collection<VersionGroup>
     */
    public function getVersionGroups(): Collection
    {
        return $this->versionGroups;
    }

    public function addVersionGroups(iterable $versionGroups): self
    {
        foreach ($versionGroups as $versionGroup) {
            $this->addVersionGroup($versionGroup);
        }

        return $this;
    }

    public function addVersionGroup(VersionGroup $versionGroup): self
    {
        if (!$this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->add($versionGroup);
        }

        return $this;
    }

    public function removeVersionGroups($versionGroups): self
    {
        foreach ($versionGroups as $versionGroup) {
            $this->removeVersionGroup($versionGroup);
        }

        return $this;
    }

    public function removeVersionGroup(VersionGroup $versionGroup): self
    {
        if ($this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->removeElement($versionGroup);
        }

        return $this;
    }
}
