<?php


namespace App\ApiPlatform\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use App\Entity\Pokemon;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Pokemon stats
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    // The Pokemon filter is required in the data provider, so this will return one member for each stat, plus total.
    paginationEnabled: false,
)]
#[ApiFilter(GroupFilter::class)]
class PokemonStat
{
    /**
     * @Groups({"read"})
     */
    #[ApiProperty(identifier: true, readableLink: false, writableLink: false)]
    private Pokemon $pokemon;

    /**
     * Stat slug, or `total` for stat total
     * @Groups({"read"})
     */
    #[ApiProperty(identifier: true)]
    private string $stat;

    /**
     * @Groups({"read"})
     */
    private int $baseValue;

    /**
     * @Groups({"read"})
     */
    private int $percentile;

    /**
     * @return Pokemon
     */
    public function getPokemon(): Pokemon
    {
        return $this->pokemon;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return PokemonStat
     */
    public function setPokemon(Pokemon $pokemon): PokemonStat
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStat(): ?string
    {
        return $this->stat;
    }

    /**
     * @param string|null $stat
     *
     * @return PokemonStat
     */
    public function setStat(?string $stat): PokemonStat
    {
        $this->stat = $stat;

        return $this;
    }

    /**
     * @return int
     */
    public function getBaseValue(): int
    {
        return $this->baseValue;
    }

    /**
     * @param int $baseValue
     *
     * @return PokemonStat
     */
    public function setBaseValue(int $baseValue): PokemonStat
    {
        $this->baseValue = $baseValue;

        return $this;
    }

    /**
     * @return int
     */
    public function getPercentile(): int
    {
        return $this->percentile;
    }

    /**
     * @param int $percentile
     *
     * @return PokemonStat
     */
    public function setPercentile(int $percentile): PokemonStat
    {
        $this->percentile = $percentile;

        return $this;
    }
}
