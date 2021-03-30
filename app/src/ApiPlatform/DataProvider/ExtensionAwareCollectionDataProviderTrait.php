<?php


namespace App\ApiPlatform\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Apply extensions to the result set
 */
trait ExtensionAwareCollectionDataProviderTrait
{
    /**
     * @param iterable<QueryCollectionExtensionInterface> $extensions
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param class-string $resourceClass
     * @param string|null $operationName
     * @param array $context
     *
     * @return iterable|null
     */
    protected function applyExtensions(
        iterable $extensions,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ): ?iterable {
        foreach ($extensions as $extension) {
            $extension->applyToCollection(
                $queryBuilder,
                $queryNameGenerator,
                $resourceClass,
                $operationName,
                $context,
            );
            if ($extension instanceof QueryResultCollectionExtensionInterface
                && $extension->supportsResult($resourceClass, $operationName, $context)) {
                return $extension->getResult($queryBuilder, $resourceClass, $operationName, $context);
            }
        }

        return null;
    }
}
