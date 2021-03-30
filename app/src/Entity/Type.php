<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TypeRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC'],
    paginationClientEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'slug' => 'exact',
])]
#[ApiFilter(BooleanFilter::class, properties: ['hidden'])]
#[ApiFilter(GroupFilter::class)]
class Type extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * The damage class this type’s moves had before Generation 4, if applicable
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveDamageClass")
     * @Groups({"read"})
     */
    private ?MoveDamageClass $damageClass;

    /**
     * Is this type normally shown to the player or used to implement a game
     * mechanic?
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private bool $hidden = false;

    public function getDamageClass(): ?MoveDamageClass
    {
        return $this->damageClass;
    }

    public function setDamageClass(?MoveDamageClass $damageClass): self
    {
        $this->damageClass = $damageClass;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }
}
