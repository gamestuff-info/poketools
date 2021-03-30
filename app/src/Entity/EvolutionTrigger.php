<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An evolution type, such as "level" or "trade".
 *
 * @ORM\Entity(repositoryClass="App\Repository\EvolutionTriggerRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']]
)]
class EvolutionTrigger extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
}
