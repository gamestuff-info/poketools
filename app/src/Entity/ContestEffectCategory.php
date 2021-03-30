<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * General categories that group contest effects
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContestEffectCategoryRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
)]
class ContestEffectCategory extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface, EntityHasDescriptionInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
    use EntityHasDescriptionTrait;
}
