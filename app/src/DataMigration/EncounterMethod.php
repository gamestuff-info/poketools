<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Encounter Method migration.
 *
 * @DataMigration(
 *     name="Encounter Method",
 *     source="/%kernel.project_dir%/resources/data/encounter_method.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\EncounterMethod",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class EncounterMethod extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * @inheritDoc
     */
    public function transform($sourceData, $destinationData)
    {
        $sourceData['slug'] = $sourceData['identifier'];
        unset($sourceData['identifier']);
        static $position = 1;
        $sourceData['position'] = $position;
        $position++;

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\EncounterMethod();
    }
}
