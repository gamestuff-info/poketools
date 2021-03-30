<?php


namespace App\ApiPlatform\DataProvider;


use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\Entity\LevelExperience;
use App\ApiPlatform\EntityHydrator;
use App\Entity\GrowthRate;
use App\Mechanic\LevelUp;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * LevelExperience CollectionDataProvider and ItemDataProvider
 */
class LevelExperienceDataProvider implements ContextAwareCollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private EntityHydrator $entityHydrator,
        private LevelUp $levelUpCalculator,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        if (!isset($filters['growthRate'])) {
            throw new NotFoundHttpException('Growth rate required');
        }
        $growthRateId = $filters['growthRate'];
        $growthRate = $this->entityHydrator->hydrateEntity($growthRateId, GrowthRate::class);
        if (!$growthRate) {
            return [];
        }

        $experience = [];
        for ($level = 1; $level <= 100; ++$level) {
            $experience[] = (new LevelExperience())
                ->setGrowthRate($growthRate)
                ->setLevel($level)
                ->setExperience($this->levelUpCalculator->experienceRequired($level, $growthRate));
        }

        return $experience;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        // Not supported
        throw new NotFoundHttpException();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === LevelExperience::class;
    }
}
