<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * The habitat of a Pokémon, as given in the FireRed/LeafGreen version Pokédex.
 *
 * Not valid for Pokémon that do not appear in FireRed/LeafGreen.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonHabitatRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']]
)]
class PokemonHabitat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasIconInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasIconTrait;
}
