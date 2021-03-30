<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Common status ailments moves can inflict on a single Pokémon, including major
 * ailments like paralysis and minor ailments like trapping.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveAilmentRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
)]
class MoveAilment extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityIsSortableTrait;

    /**
     * Does this ailment disappear after battle?
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private bool $volatile = false;

    public function isVolatile(): bool
    {
        return $this->volatile;
    }

    public function setVolatile(bool $volatile): self
    {
        $this->volatile = $volatile;

        return $this;
    }
}
