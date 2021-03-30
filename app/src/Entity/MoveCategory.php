<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * Very general categories that loosely group move effects.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveCategoryRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
)]
class MoveCategory extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface, EntityHasDescriptionInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
    use EntityHasDescriptionTrait;
}
