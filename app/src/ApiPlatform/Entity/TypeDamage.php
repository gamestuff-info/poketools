<?php


namespace App\ApiPlatform\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Type;
use App\Entity\TypeChart;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Effectiveness of types against each other.
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    paginationEnabled: false,
)]
class TypeDamage
{
    /**
     * Type Chart in use
     */
    #[ApiProperty(identifier: true, readableLink: false, writableLink: false)]
    private TypeChart $typeChart;

    /**
     * Attacking type id
     */
    #[ApiProperty(identifier: true)]
    private string $attackingTypeId;

    /**
     * Defending type ids, sorted and separated with `-`.
     */
    #[ApiProperty(identifier: true)]
    private string $defendingTypeId;

    /**
     * @Groups({"read"})
     */
    private Type $type;

    /**
     * @Groups({"read"})
     */
    private int $efficacy;

    /**
     * @return TypeChart
     */
    public function getTypeChart(): TypeChart
    {
        return $this->typeChart;
    }

    /**
     * @param TypeChart $typeChart
     *
     * @return TypeDamage
     */
    public function setTypeChart(TypeChart $typeChart): TypeDamage
    {
        $this->typeChart = $typeChart;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttackingTypeId(): string
    {
        return $this->attackingTypeId;
    }

    /**
     * @param string $attackingTypeId
     *
     * @return TypeDamage
     */
    public function setAttackingTypeId(string $attackingTypeId): TypeDamage
    {
        $this->attackingTypeId = $attackingTypeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefendingTypeId(): string
    {
        return $this->defendingTypeId;
    }

    /**
     * @param string $defendingTypeId
     *
     * @return TypeDamage
     */
    public function setDefendingTypeId(string $defendingTypeId): TypeDamage
    {
        $this->defendingTypeId = $defendingTypeId;

        return $this;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @param Type $type
     *
     * @return TypeDamage
     */
    public function setType(Type $type): TypeDamage
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getEfficacy(): int
    {
        return $this->efficacy;
    }

    /**
     * @param int $efficacy
     *
     * @return TypeDamage
     */
    public function setEfficacy(int $efficacy): TypeDamage
    {
        $this->efficacy = $efficacy;

        return $this;
    }
}
