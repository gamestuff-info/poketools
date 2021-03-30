<?php


namespace App\ApiPlatform\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Apply extensions to the result set
 */
trait ExtensionAwareItemDataProviderTrait
{
    /**
     * @param iterable<QueryItemExtensionInterface> $extensions
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param class-string $resourceClass
     * @param array $identifiers
     * @param string|null $operationName
     * @param array $context
     *
     * @return object|null
     */
    protected function applyExtensions(
        iterable $extensions,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ): ?object {
        foreach ($extensions as $extension) {
            $extension->applyToItem(
                $queryBuilder,
                $queryNameGenerator,
                $resourceClass,
                $identifiers,
                $operationName,
                $context
            );
            if ($extension instanceof QueryResultItemExtensionInterface
                && $extension->supportsResult($resourceClass, $operationName, $context)) {
                return $extension->getResult($queryBuilder, $resourceClass, $operationName, $context);
            }
        }

        return null;
    }
}
