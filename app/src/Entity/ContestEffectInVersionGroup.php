<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Effect of a move when used in a Contest.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContestEffectInVersionGroupRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
)]
class ContestEffectInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasDescriptionInterface, EntityHasFlavorTextInterface
{

    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasDescriptionTrait;
    use EntityHasFlavorTextTrait;

    /**
     * @var ContestEffect
     * @ORM\ManyToOne(targetEntity="App\Entity\ContestEffect", inversedBy="children")
     */
    protected EntityHasChildrenInterface $parent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ContestEffectCategory")
     * @Groups({"read"})
     */
    private ?ContestEffectCategory $category;

    /**
     * The base number of hearts the user of this move gets
     *
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({"read"})
     */
    private int $appeal;

    /**
     * The base number of hearts the user’s opponent loses
     *
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({"read"})
     */
    private int $jam;

    public function getCategory(): ?ContestEffectCategory
    {
        return $this->category;
    }

    public function setCategory(?ContestEffectCategory $category): ContestEffectInVersionGroup
    {
        $this->category = $category;

        return $this;
    }

    public function getAppeal(): ?int
    {
        return $this->appeal;
    }

    public function setAppeal(int $appeal): self
    {
        $this->appeal = $appeal;

        return $this;
    }

    public function getJam(): ?int
    {
        return $this->jam;
    }

    public function setJam(int $jam): self
    {
        $this->jam = $jam;

        return $this;
    }
}
