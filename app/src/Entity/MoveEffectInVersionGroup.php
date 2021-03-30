<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * An effect of a move.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveEffectInVersionGroupRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
)]
class MoveEffectInVersionGroup extends AbstractDexEntity implements EntityGroupedByVersionGroupInterface, EntityHasParentInterface, EntityHasDescriptionInterface
{

    use EntityGroupedByVersionGroupTrait;
    use EntityHasParentTrait;
    use EntityHasDescriptionTrait;

    /**
     * @var Move
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveEffect", inversedBy="children")
     */
    protected EntityHasChildrenInterface $parent;
}
