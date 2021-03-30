<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A condition in the game world that affects Pokémon encounters, such as time
 * of day.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EncounterConditionRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC']
)]
class EncounterCondition extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var Collection<EncounterConditionState>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EncounterConditionState", mappedBy="condition", cascade={"all"},
     *     fetch="EAGER")
     * @Assert\NotBlank()
     */
    private Collection $states;

    public function __construct()
    {
        $this->states = new ArrayCollection();
    }

    /**
     * @return Collection<EncounterConditionState>
     */
    public function getStates(): Collection
    {
        return $this->states;
    }

    public function addStates($states): self
    {
        foreach ($states as $state) {
            $this->addState($state);
        }

        return $this;
    }

    public function addState(EncounterConditionState $state): self
    {
        if (!$this->states->contains($state)) {
            $this->states->add($state);
            $state->setCondition($this);
        }

        return $this;
    }

    public function removeStates($states): self
    {
        foreach ($states as $state) {
            $this->removeState($state);
            $state->setCondition(null);
        }

        return $this;
    }

    public function removeState(EncounterConditionState $state): self
    {
        if ($this->states->contains($state)) {
            $this->states->removeElement($state);
        }

        return $this;
    }

    public function getDefaultState(): ?EncounterConditionState
    {
        $defaults = $this->states->filter(
            function (EncounterConditionState $state) {
                return $state->isDefault();
            }
        );

        if ($defaults->isEmpty()) {
            return null;
        } else {
            return $defaults->first();
        }
    }
}
