<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;

/**
 * A pocket in the in-game bag.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemPocketInVersionGroupRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['position' => 'ASC', 'name' => 'ASC'],
    paginationClientEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'versionGroup' => 'exact',
    'slug' => 'exact',
])]
class ItemPocketInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityHasIconInterface, EntityIsSortableInterface
{

    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasNameAndSlugTrait;
    use EntityHasIconTrait;
    use EntityIsSortableTrait;

    /**
     * @var ItemPocket
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemPocket", inversedBy="children")
     */
    protected EntityHasChildrenInterface $parent;
}
