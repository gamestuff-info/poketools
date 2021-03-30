<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A possible state for a condition.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EncounterConditionStateRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC']
)]
class EncounterConditionState extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface, EntityHasDefaultInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
    use EntityHasDefaultTrait;

    /**
     * The encounter condition this state belongs to
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EncounterCondition", inversedBy="states")
     * @Groups({"read"})
     */
    private EncounterCondition $condition;

    public function getCondition(): ?EncounterCondition
    {
        return $this->condition;
    }

    public function setCondition(EncounterCondition $condition): self
    {
        $this->condition = $condition;

        return $this;
    }
}
