<?php
/**
 * @file SlugAndVersionInterface.php
 */

namespace App\Repository;


use App\Entity\Version;

/**
 * Interface SlugAndVersionInterface
 */
interface SlugAndVersionInterface
{
    /**
     * Find an entity by slug and version.
     *
     * Returns null if there is no matching entity.
     *
     * @param string $slug
     * @param Version|string $version
     *
     * @return object|null
     */
    public function findOneByVersion(string $slug, Version|string $version);
}
