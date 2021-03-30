<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A Stat, such as Attack or Speed.
 *
 * @ORM\Entity(repositoryClass="App\Repository\StatRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
)]
class Stat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * The damage class this stat affects
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveDamageClass", fetch="EAGER")
     */
    private ?MoveDamageClass $damageClass;

    /**
     * Does this stat only apply in battle?
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private bool $battleOnly = false;

    public function getDamageClass(): ?MoveDamageClass
    {
        return $this->damageClass;
    }

    public function setDamageClass(?MoveDamageClass $damageClass): self
    {
        $this->damageClass = $damageClass;

        return $this;
    }

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
