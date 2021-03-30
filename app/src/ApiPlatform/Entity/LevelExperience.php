<?php


namespace App\ApiPlatform\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use App\Entity\GrowthRate;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Level to experience for a given growth rate
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    // The growth rate is required in the data provider.
    paginationEnabled: false
)]
#[ApiFilter(GroupFilter::class)]
class LevelExperience
{
    /**
     * @Groups({"read"})
     */
    #[ApiProperty(identifier: true, readableLink: false, writableLink: false)]
    private GrowthRate $growthRate;

    /**
     * @Groups({"read"})
     */
    #[ApiProperty(identifier: true)]
    private int $level;

    /**
     * @Groups({"read"})
     */
    private int $experience;

    /**
     * @return GrowthRate
     */
    public function getGrowthRate(): GrowthRate
    {
        return $this->growthRate;
    }

    /**
     * @param GrowthRate $growthRate
     *
     * @return LevelExperience
     */
    public function setGrowthRate(GrowthRate $growthRate): LevelExperience
    {
        $this->growthRate = $growthRate;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return LevelExperience
     */
    public function setLevel(int $level): LevelExperience
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return int
     */
    public function getExperience(): int
    {
        return $this->experience;
    }

    /**
     * @param int $experience
     *
     * @return LevelExperience
     */
    public function setExperience(int $experience): LevelExperience
    {
        $this->experience = $experience;

        return $this;
    }
}
