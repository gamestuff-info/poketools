<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A weather condition in the overworld and/or battle.
 *
 * @ORM\Entity(repositoryClass="App\Repository\WeatherRepository")
 */
class Weather extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $battleOnly = false;

    public function isBattleOnly(): bool
    {
        return $this->battleOnly;
    }

    public function setBattleOnly(bool $battleOnly): self
    {
        $this->battleOnly = $battleOnly;

        return $this;
    }
}
