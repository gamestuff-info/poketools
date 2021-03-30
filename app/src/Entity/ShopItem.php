<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shop inventory item
 *
 * @ORM\Entity(repositoryClass="App\Repository\ShopItemRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['shop.name' => 'ASC', 'position' => 'ASC'],
)]
#[ApiFilter(GroupFilter::class)]
class ShopItem extends AbstractDexEntity implements EntityIsSortableInterface
{
    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shop", inversedBy="items")
     * @Groups({"item_view"})
     */
    private Shop $shop;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup", inversedBy="inShops", fetch="EAGER")
     * @Assert\NotNull()
     * @Groups({"location_view"})
     */
    private ItemInVersionGroup $item;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     * @Groups({"read"})
     */
    private ?int $buy;

    public function getItem(): ?ItemInVersionGroup
    {
        return $this->item;
    }

    public function setItem(ItemInVersionGroup $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getBuy(): ?int
    {
        return $this->buy;
    }

    public function setBuy(?int $buy): self
    {
        $this->buy = $buy;

        return $this;
    }

    public function getShop(): Shop
    {
        return $this->shop;
    }

    public function setShop(Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }
}
