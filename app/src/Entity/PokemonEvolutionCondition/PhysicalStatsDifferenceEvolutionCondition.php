<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The value of attack - defense must match this.
 *
 * @ORM\Entity()
 */
class PhysicalStatsDifferenceEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     */
    private int $physicalStatsDifference;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match (true) {
            $this->physicalStatsDifference < 0 => 'Attack &lt; Defense',
            $this->physicalStatsDifference > 0 => 'Attack &gt; Defense',
            default => 'Attack = Defense',
        };
    }

    public function getPhysicalStatsDifference(): ?int
    {
        return $this->physicalStatsDifference;
    }

    public function setPhysicalStatsDifference(int $physicalStatsDifference): self
    {
        $this->physicalStatsDifference =  $physicalStatsDifference;

        return $this;
    }
}
