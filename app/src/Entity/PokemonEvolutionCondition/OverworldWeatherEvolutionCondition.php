<?php


namespace App\Entity\PokemonEvolutionCondition;

use App\Entity\PokemonEvolutionCondition;
use App\Entity\Weather;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This weather must be present in the overworld; battle weather does not count.
 *
 * @ORM\Entity()
 */
class OverworldWeatherEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Weather")
     * @Assert\NotBlank()
     */
    private Weather $overworldWeather;

    public function getLabel(): string
    {
        return sprintf('[]{mechanic:%s} in the overworld', $this->getOverworldWeather()->getSlug());
    }

    public function getOverworldWeather(): ?Weather
    {
        return $this->overworldWeather;
    }

    public function setOverworldWeather(Weather $overworldWeather): self
    {
        $this->overworldWeather = $overworldWeather;

        return $this;
    }
}
