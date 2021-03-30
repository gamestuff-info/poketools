<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonWildHeldItemRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'item' => 'exact',
    'pokemon' => 'exact',
    'version' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'item.name',
    'pokemon.abilities.ability.name',
    'pokemon.attack.baseValue',
    'pokemon.defense.baseValue',
    'pokemon.hp.baseValue',
    'pokemon.name',
    'pokemon.position',
    'pokemon.position',
    'pokemon.special.baseValue',
    'pokemon.specialAttack.baseValue',
    'pokemon.specialDefense.baseValue',
    'pokemon.species.position',
    'pokemon.species.position',
    'pokemon.speed.baseValue',
    'pokemon.statTotal',
    'pokemon.types.type.position',
    'rate',
])]
class PokemonWildHeldItem
{
    /**
     * Unique Id
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="wildHeldItems")
     * @Groups({"item_view"})
     */
    private Pokemon $pokemon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Version")
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    private Version $version;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup", inversedBy="pokemonHoldsInWild", fetch="EAGER")
     * @Groups({"pokemon_view"})
     */
    private ItemInVersionGroup $item;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="100")
     * @Groups({"read"})
     */
    private int $rate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(Version $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getItem(): ?ItemInVersionGroup
    {
        return $this->item;
    }

    public function setItem(ItemInVersionGroup $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }
}
