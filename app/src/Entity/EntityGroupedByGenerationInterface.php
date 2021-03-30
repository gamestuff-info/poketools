<?php


namespace App\Entity;

/**
 * Entities groupable by Generation.
 */
interface EntityGroupedByGenerationInterface extends EntityGroupedInterface
{
    public function getGeneration(): ?Generation;

    public function setGeneration(Generation $generation);

    public function getGroup(): GroupableInterface;
}
