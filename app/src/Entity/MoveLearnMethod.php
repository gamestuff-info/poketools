<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * A method a move can be learned by, such as "Level up" or "Tutor".
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveLearnMethodRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => 'read'],
    order: ['position' => 'ASC'],
    paginationClientEnabled: true,
    forceEager: false,
)]
class MoveLearnMethod extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityIsSortableInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityIsSortableTrait;
}
