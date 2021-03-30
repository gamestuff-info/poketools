<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A TM or HM; numbered item that can teach a move to a Pokémon.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MachineRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
)]
class Machine extends AbstractDexEntity
{

    // TODO: Enum
    public const MACHINE_TM = 'TM';
    public const MACHINE_HM = 'HM';
    private const MACHINE_TYPES = [self::MACHINE_TM, self::MACHINE_HM];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemInVersionGroup", mappedBy="machine")
     * @Groups({"move_view"})
     */
    private ItemInVersionGroup $item;

    /**
     * @ORM\Column(type="string")
     * @Assert\Choice(callback="validMachineTypes")
     * @Groups({"read"})
     */
    private string $type;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $number;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\MoveInVersionGroup", inversedBy="machine", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"item_view"})
     */
    private MoveInVersionGroup $move;

    /**
     * Get a list of valid machine types for use with validation.
     *
     * @return array
     * @internal
     */
    public static function validMachineTypes(): array
    {
        return self::MACHINE_TYPES;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getMove(): ?MoveInVersionGroup
    {
        return $this->move;
    }

    public function setMove(MoveInVersionGroup $move): self
    {
        $this->move = $move;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->getItem()->getName();
    }

    public function getItem(): ?ItemInVersionGroup
    {
        return $this->item;
    }

    public function setItem(ItemInVersionGroup $item): self
    {
        $this->item = $item;
        $item->setMachine($this);

        return $this;
    }
}
