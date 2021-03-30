<?php

namespace App\Entity;

use App\Entity\Embeddable\Range;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonFormPokeathlonStatRepository")
 */
class PokemonFormPokeathlonStat implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonForm", inversedBy="pokeathlonStats")
     * @ORM\Id()
     */
    private PokemonForm $pokemonForm;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PokeathlonStat")
     * @ORM\Id()
     * @Groups({"read"})
     */
    private PokeathlonStat $pokeathlonStat;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     * @Assert\NotBlank()
     * @Assert\Expression("value.getMin() >= 0 && value.getMax() <= 5")
     * @Groups({"read"})
     */
    private Range $range;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="5")
     * @Groups({"read"})
     */
    private int $baseValue;

    public function getPokemonForm(): ?PokemonForm
    {
        return $this->pokemonForm;
    }

    public function setPokemonForm(PokemonForm $pokemonForm): self
    {
        $this->pokemonForm = $pokemonForm;

        return $this;
    }

    public function getPokeathlonStat(): ?PokeathlonStat
    {
        return $this->pokeathlonStat;
    }

    public function setPokeathlonStat(PokeathlonStat $pokeathlonStat): self
    {
        $this->pokeathlonStat = $pokeathlonStat;
        $this->setPosition($pokeathlonStat->getPosition());

        return $this;
    }

    public function getRange(): ?Range
    {
        return $this->range;
    }

    /**
     * @Groups({"read"})
     */
    public function getMin(): ?int {
        return $this->range->getMin();
    }

    /**
     * @Groups({"read"})
     */
    public function getMax(): ?int {
        return $this->range->getMax();
    }

    public function setRange(Range $range): self
    {
        $this->range = $range;

        return $this;
    }

    public function getBaseValue(): ?int
    {
        return $this->baseValue;
    }

    public function setBaseValue(int $baseValue): self
    {
        $this->baseValue = $baseValue;

        return $this;
    }
}
