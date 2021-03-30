<?php


namespace App\ApiPlatform\DataProvider;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\MoveInVersionGroup;
use App\Entity\Version;
use App\Repository\MoveInVersionGroupRepository;

/**
 * MoveInVersionGroup CollectionDataProvider
 */
class MoveInVersionGroupDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    use ExtensionAwareCollectionDataProviderTrait;

    /**
     * CollectionDataProvider constructor.
     *
     * @param MoveInVersionGroupRepository $moveRepo
     * @param iterable<ContextAwareQueryCollectionExtensionInterface> $collectionExtensions
     */
    public function __construct(
        private MoveInVersionGroupRepository $moveRepo,
        private iterable $collectionExtensions,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        // Use the type's damage class for filtering/ordering if applicable to this version
        /** @var Version $version */
        $version = $context['_useVersion'] ?? null;
        if ($version) {
            $hasMoveDamageClass = $version->getVersionGroup()->hasFeatureString('move-damage-class');
            if (!$hasMoveDamageClass && isset($context['filters'])) {
                // Change filter
                if (isset($context['filters']['damageClass'])) {
                    $context['filters']['type.damageClass'] = $context['filters']['damageClass'];
                    unset($context['filters']['damageClass']);
                }
                // Change order
                if (isset($context['filters']['order'], $context['filters']['order']['damageClass.position'])) {
                    $context['filters']['order']['type.damageClass.position'] = $context['filters']['order']['damageClass.position'];
                    unset($context['filters']['order']['damageClass.position']);
                }
            }
        }

        $qb = $this->moveRepo->createQueryBuilder('move_in_version_group');
        $nameGenerator = new QueryNameGenerator();

        return $this->applyExtensions(
                $this->collectionExtensions,
                $qb,
                $nameGenerator,
                $resourceClass,
                $operationName,
                $context
            )
            ?? $qb->getQuery()->getResult();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $operationName === 'get' && $resourceClass === MoveInVersionGroup::class;
    }
}
