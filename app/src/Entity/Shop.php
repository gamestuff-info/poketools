<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Item shop (e.g. Poke Mart)
 *
 * @ORM\Entity(repositoryClass="App\Repository\ShopRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['locationArea.location.name' => 'ASC', 'locationArea.position' => 'ASC', 'position' => 'ASC'],
)]
#[ApiFilter(GroupFilter::class)]
class Shop extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationArea", inversedBy="shops")
     * @Groups({"item_view"})
     */
    private LocationArea $locationArea;

    /**
     * @var Collection<ShopItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ShopItem", mappedBy="shop", fetch="EAGER")
     * @ORM\OrderBy({"position": "ASC"})
     */
    #[ApiSubresource(maxDepth: 1)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getLocationArea(): LocationArea
    {
        return $this->locationArea;
    }

    public function setLocationArea(LocationArea $locationArea): Shop
    {
        $this->locationArea = $locationArea;

        return $this;
    }

    /**
     * @return Collection<ShopItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ShopItem $shopItem): self
    {
        if (!$this->items->contains($shopItem)) {
            $this->items->add($shopItem);
            $shopItem->setShop($this);
        }

        return $this;
    }

    public function removeItem(ShopItem $shopItem): self
    {
        if ($this->items->contains($shopItem)) {
            $this->items->removeElement($shopItem);
            $shopItem->setShop(null);
        }

        return $this;
    }
}
