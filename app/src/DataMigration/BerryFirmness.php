<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Berry Firmness migration.
 *
 * @DataMigration(
 *     name="Berry Firmness",
 *     source="/%kernel.project_dir%/resources/data/berry_firmness.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\BerryFirmness",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class BerryFirmness extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * @inheritDoc
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\BerryFirmness();
    }
}
