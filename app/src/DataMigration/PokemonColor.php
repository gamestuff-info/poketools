<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Pokemon Color migration.
 *
 * @DataMigration(
 *     name="Pokemon Color",
 *     source="/%kernel.project_dir%/resources/data/pokemon_color.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\PokemonColor",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class PokemonColor extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * @inheritDoc
     */
    public function transform($sourceData, $destinationData)
    {
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
        return new \App\Entity\PokemonColor();
    }
}
