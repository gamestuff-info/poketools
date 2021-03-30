<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An effect a move can have when used in the Super Contest.
 *
 * @ORM\Entity(repositoryClass="App\Repository\SuperContestEffectRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
)]
class SuperContestEffect extends AbstractDexEntity implements EntityHasFlavorTextInterface, EntityHasDescriptionInterface
{

    use EntityHasFlavorTextTrait;
    use EntityHasDescriptionTrait;

    /**
     * The number of hearts the user gains
     *
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({"read"})
     */
    private int $appeal;

    public function getAppeal(): ?int
    {
        return $this->appeal;
    }

    public function setAppeal(int $appeal): self
    {
        $this->appeal = $appeal;

        return $this;
    }
}
