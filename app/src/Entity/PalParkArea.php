<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * A distinct area of Pal Park in which Pokémon appear.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PalParkAreaRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
)]
class PalParkArea extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
