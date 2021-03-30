<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Pokémon gender
 *
 * @ORM\Entity(repositoryClass="App\Repository\GenderRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC']
)]
class Gender extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
}
