<?php


namespace App\ApiPlatform\DataProvider;


use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\Entity\TypeDamage;
use App\ApiPlatform\EntityHydrator;
use App\Entity\Type;
use App\Entity\TypeChart;
use App\Entity\VersionGroup;
use App\Repository\TypeChartRepository;
use App\Repository\TypeEfficacyRepository;
use Ds\Map;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * TypeDamage Data Provider
 */
class TypeDamageDataProvider implements ContextAwareCollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private TypeChartRepository $typeChartRepo,
        private TypeEfficacyRepository $typeEfficacyRepo,
        private EntityHydrator $entityHydrator,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $context['filters'] ?? [];
        // Types
        $attackingType = $filters['attackingType'] ?? null;
        $defendingTypes = $filters['defendingType'] ?? [];
        if (!$attackingType && !$defendingTypes) {
            throw new NotFoundHttpException('attackingType or defendingType required.');
        } elseif ($attackingType && $defendingTypes) {
            throw new NotFoundHttpException('Only one of attackingType or defendingType may be specified.');
        }
        // Type chart
        if (isset($filters['typeChart'])) {
            $typeChart = $this->entityHydrator->hydrateEntity($filters['typeChart'], TypeChart::class);
            if (!$typeChart) {
                return [];
            }
        } elseif (isset($filters['versionGroup'])) {
            $versionGroup = $this->entityHydrator->hydrateEntityOrId($filters['versionGroup'], VersionGroup::class);
            if (!$versionGroup) {
                return [];
            }
            $typeChart = $this->typeChartRepo->findOneByVersionGroup($versionGroup);
            if (!$typeChart) {
                return [];
            }
        } else {
            throw new NotFoundHttpException('typeChart or versionGroup required.');
        }

        // Attacking type
        if ($attackingType) {
            $attackingType = $this->entityHydrator->hydrateEntity($attackingType, Type::class);
            if (!$attackingType) {
                return [];
            }
        }
        // Defending type
        if (!is_array($defendingTypes)) {
            $defendingTypes = [$defendingTypes];
        }
        foreach ($defendingTypes as &$defendingType) {
            $defendingType = $this->entityHydrator->hydrateEntity($defendingType, Type::class);
            if (!$defendingType) {
                // Bad type
                return [];
            }
        }
        unset($defendingType);

        // Get efficacies
        /** @var Map<int, Map<int, int>> $efficacies attacking > defending > efficacy */
        $efficacies = new Map();
        /** @var Map<int, Type> $types */
        $types = new Map();
        foreach ($this->typeEfficacyRepo->findBy(['typeChart' => $typeChart]) as $typeEfficacy) {
            if (!$efficacies->hasKey($typeEfficacy->getAttackingType()->getId())) {
                $efficacies->put($typeEfficacy->getAttackingType()->getId(), new Map());
            }
            $efficacies->get($typeEfficacy->getAttackingType()->getId())->put(
                $typeEfficacy->getDefendingType()->getId(),
                $typeEfficacy->getEfficacy()
            );
            if (!$types->hasKey($typeEfficacy->getAttackingType()->getId())) {
                $types->put($typeEfficacy->getAttackingType()->getId(), $typeEfficacy->getAttackingType());
            }
        }

        // Calculate efficacy
        $results = [];
        if ($attackingType) {
            // Attacking
            foreach ($efficacies->get($attackingType->getId()) as $defendingTypeId => $efficacy) {
                /** @var Type $efficacyDefendingType */
                $efficacyDefendingType = $types->get($defendingTypeId);
                if ($efficacyDefendingType->isHidden() && $efficacyDefendingType->getId() !== $attackingType->getId()) {
                    // Skip hidden types that are not the same as the attacking type.
                    continue;
                }
                $results[] = (new TypeDamage())
                    ->setTypeChart($typeChart)
                    ->setAttackingTypeId((string)$attackingType->getId())
                    ->setDefendingTypeId((string)$defendingTypeId)
                    ->setType($efficacyDefendingType)
                    ->setEfficacy($efficacy);
            }
        } elseif ($defendingTypes) {
            // Defending
            // For IRI, use the defending type ids separated by a hyphen.
            $defendingTypeIds = array_map(fn(Type $type) => $type->getId(), $defendingTypes);
            sort($defendingTypeIds);
            $defendingTypeId = implode('-', $defendingTypeIds);
            foreach ($efficacies as $attackingTypeId => $defendingTypesEfficacy) {
                /** @var Type $efficacyAttackingType */
                $efficacyAttackingType = $types->get($attackingTypeId);
                if ($efficacyAttackingType->isHidden() && !in_array(
                        $efficacyAttackingType->getId(),
                        $defendingTypeIds
                    )) {
                    // Skip hidden types that are not the same as a defending type.
                    continue;
                }
                $efficacy = 1.0;
                foreach ($defendingTypes as $defendingType) {
                    $efficacy *= $defendingTypesEfficacy->get($defendingType->getId()) / 100.0;
                }
                $results[] = (new TypeDamage())
                    ->setTypeChart($typeChart)
                    ->setAttackingTypeId((string)$attackingTypeId)
                    ->setDefendingTypeId($defendingTypeId)
                    ->setType($efficacyAttackingType)
                    ->setEfficacy((int)round($efficacy * 100.0));
            }
        }
        usort(
            $results,
            fn(TypeDamage $a, TypeDamage $b) => $a->getType()->getPosition() - $b->getType()->getPosition()
        );

        return $results;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        // Not supported
        throw new NotFoundHttpException();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === TypeDamage::class;
    }
}
