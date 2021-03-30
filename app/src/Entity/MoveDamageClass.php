<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * Any of the damage classes moves can have, i.e. physical, special, or
 * non-damaging.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveDamageClassRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
)]
class MoveDamageClass extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityIsSortableInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityIsSortableTrait;
}
