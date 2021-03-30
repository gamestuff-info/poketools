<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Time of Day
 *
 * @ORM\Entity(repositoryClass="App\Repository\TimeOfDayRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'generation' => 'exact',
])]
class TimeOfDay extends AbstractDexEntity implements EntityGroupedByGenerationInterface, EntityIsSortableInterface, EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityGroupedByGenerationTrait;
    use EntityIsSortableTrait;
    use EntityHasNameAndSlugTrait;

    /**
     * @ORM\Column(type="chronos_time")
     * @Assert\NotBlank()
     */
    private Chronos $starts;

    /**
     * @ORM\Column(type="chronos_time")
     * @Assert\NotBlank()
     */
    private Chronos $ends;

    public function getStarts(): ?Chronos
    {
        return $this->starts;
    }

    public function getStartsIso8601(): ?string
    {
        return $this->starts?->toIso8601String();
    }

    public function setStarts(Chronos $starts): self
    {
        $this->starts = $starts;

        return $this;
    }

    public function getEnds(): ?Chronos
    {
        return $this->ends;
    }

    public function getEndsIso8601(): ?string
    {
        return $this->ends?->toIso8601String();
    }

    public function setEnds(Chronos $ends): self
    {
        $this->ends = $ends;

        return $this;
    }
}
