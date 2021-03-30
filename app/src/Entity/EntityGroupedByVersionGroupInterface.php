<?php


namespace App\Entity;

/**
 * Entities groupable by Version Group.
 */
interface EntityGroupedByVersionGroupInterface extends EntityGroupedInterface
{
    public function getVersionGroup(): ?VersionGroup;

    public function setVersionGroup(VersionGroup $versionGroup);

    public function getGroup(): GroupableInterface;
}
