<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A nature a Pokémon can have, such as Calm or Brave.
 *
 * @ORM\Entity(repositoryClass="App\Repository\NatureRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['name' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'versionGroup' => 'exact',
    'slug' => 'exact',
])]
#[ApiFilter(GroupFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
    'statIncreased.position',
    'statDecreased.position',
    'flavorLikes.name',
    'flavorLikes.contestType.position',
    'flavorHates.name',
    'flavorHates.contestType.position',
])]
class Nature extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

    /**
     * The stat that this nature increases by 10%
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private Stat $statIncreased;

    /**
     * The stat that this nature decreases by 10%
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private Stat $statDecreased;

    /**
     * The Berry flavor the Pokémon likes
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BerryFlavor", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private BerryFlavor $flavorLikes;

    /**
     * The Berry flavor the Pokémon hates
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BerryFlavor", fetch="EAGER")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private BerryFlavor $flavorHates;

    /**
     * @var Collection<NatureBattleStylePreference>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\NatureBattleStylePreference", mappedBy="nature", cascade={"ALL"})
     * @Groups({"nature_view"})
     */
    private Collection $battleStylePreferences;

    /**
     * @var Collection<NaturePokeathlonStatChange>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\NaturePokeathlonStatChange", mappedBy="nature", cascade={"ALL"})
     * @Groups({"nature_view"})
     */
    private Collection $pokeathlonStatChanges;

    public function __construct()
    {
        $this->battleStylePreferences = new ArrayCollection();
        $this->pokeathlonStatChanges = new ArrayCollection();
    }

    public function addBattleStylePreference(NatureBattleStylePreference $battleStylePreference): self
    {
        if (!$this->battleStylePreferences->contains($battleStylePreference)) {
            $this->battleStylePreferences->add($battleStylePreference);
            $battleStylePreference->setNature($this);
        }

        return $this;
    }

    public function removeBattleStylePreferences(NatureBattleStylePreference $battleStylePreference): self
    {
        if ($this->battleStylePreferences->contains($battleStylePreference)) {
            $this->battleStylePreferences->removeElement($battleStylePreference);
            $battleStylePreference->setNature(null);
        }

        return $this;
    }

    /**
     * @return Collection<NatureBattleStylePreference>
     */
    public function getBattleStylePreferences(): Collection
    {
        return $this->battleStylePreferences;
    }

    public function addPokeathlonStatChange(NaturePokeathlonStatChange $pokeathlonStatChange): self
    {
        if (!$this->pokeathlonStatChanges->contains($pokeathlonStatChange)) {
            $this->pokeathlonStatChanges->add($pokeathlonStatChange);
            $pokeathlonStatChange->setNature($this);
        }

        return $this;
    }

    public function removePokeathlonStatChange(NaturePokeathlonStatChange $pokeathlonStatChange): self
    {
        if ($this->pokeathlonStatChanges->contains($pokeathlonStatChange)) {
            $this->pokeathlonStatChanges->removeElement($pokeathlonStatChange);
            $pokeathlonStatChange->setNature(null);
        }

        return $this;
    }

    /**
     * @return Collection<NaturePokeathlonStatChange>
     */
    public function getPokeathlonStatChanges(): Collection
    {
        return $this->pokeathlonStatChanges;
    }

    /**
     *
     * A Nature is neutral if it does not affect any stats or flavor preferences
     *
     * @return bool
     * @Groups({"read"})
     */
    public function isNeutral(): bool
    {
        return ($this->getStatIncreased()->getId() === $this->getStatDecreased()->getId()
            && $this->getFlavorLikes()->getId() === $this->getFlavorHates()->getId());
    }

    public function getStatIncreased(): ?Stat
    {
        return $this->statIncreased;
    }

    public function setStatIncreased(Stat $statIncreased): self
    {
        $this->statIncreased = $statIncreased;

        return $this;
    }

    public function getStatDecreased(): ?Stat
    {
        return $this->statDecreased;
    }

    public function setStatDecreased(Stat $statDecreased): self
    {
        $this->statDecreased = $statDecreased;

        return $this;
    }

    public function getFlavorLikes(): ?BerryFlavor
    {
        return $this->flavorLikes;
    }

    public function setFlavorLikes(BerryFlavor $flavorLikes): self
    {
        $this->flavorLikes = $flavorLikes;

        return $this;
    }

    public function getFlavorHates(): ?BerryFlavor
    {
        return $this->flavorHates;
    }

    public function setFlavorHates(BerryFlavor $flavorHates): self
    {
        $this->flavorHates = $flavorHates;

        return $this;
    }
}
