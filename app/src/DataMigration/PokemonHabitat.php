<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Pokemon Habitat migration.
 *
 * @DataMigration(
 *     name="Pokemon Habitat",
 *     source="/%kernel.project_dir%/resources/data/pokemon_habitat.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\PokemonHabitat",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class PokemonHabitat extends AbstractDoctrineDataMigration implements DataMigrationInterface
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
        return new \App\Entity\PokemonHabitat();
    }
}
