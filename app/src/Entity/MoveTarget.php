<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * Targeting or "range" of a move, e.g. "Affects all opponents" or "Affects user".
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveTargetRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC']
)]
class MoveTarget extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
}
