<?php


namespace App\Entity\PokemonEvolutionCondition;

use App\Entity\ItemInVersionGroup;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An item must be in the player's bag.
 *
 * @ORM\Entity()
 */
class ItemInBagEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup")
     * @Assert\NotBlank()
     */
    private ItemInVersionGroup $bagItem;

    public function getLabel(): string
    {
        return sprintf('[]{item:%s} in the bag', $this->getBagItem()->getSlug());
    }

    public function getBagItem(): ?ItemInVersionGroup
    {
        return $this->bagItem;
    }

    public function setBagItem(ItemInVersionGroup $bagItem): self
    {
        $this->bagItem = $bagItem;

        return $this;
    }
}
