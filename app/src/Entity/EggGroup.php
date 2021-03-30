<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Egg group. Usually, two Pokémon can breed if they share an Egg Group.
 *
 * Exceptions:
 * - Pokémon in the No Eggs group cannot breed.
 * - Pokemon in the Ditto group can breed with any pokemon except those in the
 *   Ditto or No Eggs groups.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EggGroupRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC']
)]
class EggGroup extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{
    use EntityHasNameAndSlugTrait;
}
