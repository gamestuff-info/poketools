<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * Battle Palace style.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BattleStyleRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
)]
class BattleStyle extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{
    use EntityHasNameAndSlugTrait;
}
