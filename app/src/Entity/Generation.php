<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Generation of the Pokémon franchise.
 *
 * @ORM\Entity(repositoryClass="App\Repository\GenerationRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
)]
class Generation extends AbstractDexEntity implements GroupableInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * Generation number
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private int $number;

    /**
     * Version groups that are part of this generation
     *
     * @var Collection<VersionGroup>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VersionGroup", mappedBy="generation")
     */
    private Collection $versionGroups;

    public function __construct()
    {
        $this->versionGroups = new ArrayCollection();
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return Collection<VersionGroup>
     */
    public function getVersionGroups()
    {
        return $this->versionGroups;
    }

    public function addVersionGroup(VersionGroup $versionGroup): self
    {
        if (!$this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->add($versionGroup);
            $versionGroup->setGeneration($this);
        }

        return $this;
    }

    public function addVersionGroups($versionGroups): self
    {
        foreach ($versionGroups as $versionGroup) {
            $this->addVersionGroup($versionGroup);
        }

        return $this;
    }

    public function removeVersionGroup(VersionGroup $versionGroup): self
    {
        if ($this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->removeElement($versionGroup);
            $versionGroup->setGeneration(null);
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
}
