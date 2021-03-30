<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Move attribute such as "snatchable" or "contact".
 *
 * @todo Make these tied to version groups.  Many don't apply to early versions.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveFlagRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
)]
class MoveFlag extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
}
