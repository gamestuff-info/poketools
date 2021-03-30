<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A berry flavor associated with a contest type.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BerryFlavorRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC'],
)]
class BerryFlavor extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

    /**
     * The corresponding Contest type
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ContestType", mappedBy="berryFlavor")
     * @Groups({"nature_index", "nature_view"})
     */
    private ContestType $contestType;

    public function getContestType(): ContestType
    {
        return $this->contestType;
    }

    public function setContestType(ContestType $contestType): BerryFlavor
    {
        $this->contestType = $contestType;
        $contestType->setBerryFlavor($this);

        return $this;
    }
}
