<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * A way the player can enter a wild encounter.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EncounterMethodRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => 'read'],
    order: ['position' => 'ASC'],
    paginationClientEnabled: true,
    forceEager: false,
)]
class EncounterMethod extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
}
