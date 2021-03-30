<?php


namespace App\Entity;

/**
 * Entities groupable by Version.
 */
interface EntityGroupedByVersionInterface extends EntityGroupedInterface
{
    public function getVersion(): Version;

    public function setVersion(Version $version);

    public function getGroup(): GroupableInterface;
}
