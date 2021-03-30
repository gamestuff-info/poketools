<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Effect of a move when used in a Contest.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContestEffectRepository")
 */
class ContestEffect extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * Unique Id
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     */
    protected int $id;

    /**
     * @var Collection<MoveEffectInVersionGroup>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ContestEffectInVersionGroup", mappedBy="parent", cascade={"all"}, fetch="EAGER")
     */
    protected Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
