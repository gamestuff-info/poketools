<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The efficacy of one type against another
 *
 * @ORM\Entity(repositoryClass="App\Repository\TypeEfficacyRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    order: ['attackingType.position' => 'ASC', 'defendingType.position' => 'ASC'],
    paginationClientEnabled: true,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'typeChart' => 'exact',
    'attackingType' => 'exact',
    'defendingType' => 'exact',
])]
#[ApiFilter(PropertyFilter::class)]
class TypeEfficacy implements EntityIsSortableInterface
{
    // This allows the type chart to be pre-sorted during data migration and avoid having to
    // join with the types.
    use EntityIsSortableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TypeChart", inversedBy="efficacies")
     * @ORM\Id()
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    private TypeChart $typeChart;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @ORM\Id()
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    private Type $attackingType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @ORM\Id()
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    private Type $defendingType;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="200")
     * @Groups({"read"})
     */
    private int $efficacy;

    public function getTypeChart(): ?TypeChart
    {
        return $this->typeChart;
    }

    public function setTypeChart(TypeChart $typeChart): self
    {
        $this->typeChart = $typeChart;

        return $this;
    }

    public function getAttackingType(): ?Type
    {
        return $this->attackingType;
    }

    public function setAttackingType(Type $attackingType): self
    {
        $this->attackingType = $attackingType;

        return $this;
    }

    public function getDefendingType(): ?Type
    {
        return $this->defendingType;
    }

    public function setDefendingType(Type $defendingType): self
    {
        $this->defendingType = $defendingType;

        return $this;
    }

    public function getEfficacy(): ?int
    {
        return $this->efficacy;
    }

    public function setEfficacy(int $efficacy): self
    {
        $this->efficacy = $efficacy;

        return $this;
    }
}
