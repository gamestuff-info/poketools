<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Stat migration.
 *
 * @DataMigration(
 *     name="Stat",
 *     source="/%kernel.project_dir%/resources/data/stat.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\Stat",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\MoveDamageClass"}
 * )
 */
class Stat extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * @inheritDoc
     * @param \App\Entity\Stat $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        static $position = 1;
        $sourceData['position'] = $position;
        $position++;
        if ($sourceData['damage_class']) {
            $sourceData['damage_class'] = $this->referenceStore->get(MoveDamageClass::class, ['identifier' => $sourceData['damage_class']]);
        } else {
            $sourceData['damage_class'] = null;
        }

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\Stat();
    }
}
