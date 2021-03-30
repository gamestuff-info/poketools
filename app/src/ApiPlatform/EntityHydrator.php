<?php


namespace App\ApiPlatform;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Get entities from API IDs
 */
class EntityHydrator
{
    public function __construct(
        private IriConverterInterface $iriConverter,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Hydrate a full entity.
     *
     * @template T
     *
     * @param string|int $id
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function hydrateEntity(string|int $id, string $className): ?object
    {
        if (self::probablyIsIri($id)) {
            // IRI
            return $this->hydrateIri($id, $className);
        } elseif (self::probablyIsEntityId($id)) {
            // Entity id
            $repo = $this->em->getRepository($className);

            return $repo->find((int)$id);
        }

        return null;
    }

    /**
     * Hydrate an entity or its ID.
     *
     * Useful when the result is only used for query joins.
     *
     * @template T
     *
     * @param string|int $id
     * @param class-string<T> $className
     *
     * @return T|int|null
     */
    public function hydrateEntityOrId(string|int $id, string $className): object|int|null
    {
        if (self::probablyIsIri($id)) {
            // IRI
            return $this->hydrateIri($id, $className);
        } elseif (self::probablyIsEntityId($id)) {
            // Entity id
            return (int)$id;
        }

        return null;
    }

    /**
     * Is the ID likely to be an IRI?
     *
     * @param string|int $id
     *
     * @return bool
     */
    static private function probablyIsIri(string|int $id): bool
    {
        return is_string($id) && !is_numeric($id);
    }

    /**
     * Is the ID likely to be an entity ID?
     *
     * @param string|int $id
     *
     * @return bool
     */
    static private function probablyIsEntityId(string|int $id): bool
    {
        return (is_string($id) && is_numeric($id)) || is_int($id);
    }

    /**
     * @template T
     *
     * @param string $iri
     * @param class-string<T> $className
     *
     * @return T|null
     */
    private function hydrateIri(string $iri, string $className): ?object
    {
        try {
            $result = $this->iriConverter->getItemFromIri($iri);
            if (get_class($result) !== $className) {
                // Unexpected IRI passed
                return null;
            }

            return $result;
        } catch (ItemNotFoundException | InvalidArgumentException) {
            // Entity doesn't exist or IRI is ill-formed
            return null;
        }
    }
}
