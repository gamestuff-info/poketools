<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Item Category migration.
 *
 * @DataMigration(
 *     name="Item Category",
 *     source="/%kernel.project_dir%/resources/data/item_category.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\ItemCategory",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     flush=true
 * )
 */
class ItemCategory extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * @inheritDoc
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        if (!empty($sourceData['parent'])) {
            $sourceData['tree_parent'] = $this->referenceStore->get(ItemCategory::class, ['identifier' => $sourceData['parent']], true);
        } else {
            $sourceData['tree_parent'] = null;
        }
        unset($sourceData['parent']);

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\ItemCategory();
    }
}
