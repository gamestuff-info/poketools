<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MoveRepository")
 *
 * @method Collection|MoveInVersionGroup[] getChildren()
 * @method self addChild(MoveInVersionGroup $child)
 * @method self addChildren(Collection|MoveInVersionGroup[] $children)
 * @method self removeChild(MoveInVersionGroup $child)
 * @method self removeChildren(Collection|MoveInVersionGroup[] $children)
 * @method MoveInVersionGroup findChildByGrouping(VersionGroup $group)
 */
class Move extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * @var Collection<MoveInVersionGroup>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MoveInVersionGroup", mappedBy="parent", cascade={"all"}, fetch="EAGER")
     */
    protected Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
