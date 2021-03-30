<?php

namespace App\DataMigration;

use App\A2B\Drivers\Destination\DbalDestinationDriver;
use App\DataMigration\Helpers\Normalizer;
use App\DataMigration\Helpers\PokemonLookup;
use App\Entity\Embeddable\Range;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\DataMigration\MigrationReferenceStoreInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Exception\MigrationException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Encounter migration.
 *
 * @DataMigration(
 *     name="Encounter",
 *     source="/%kernel.project_dir%/resources/data/encounter.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="id")},
 *     destination="encounter",
 *     destinationIds={@IdField(name="id")},
 *     destinationDriver="App\A2B\Drivers\Destination\DbalDestinationDriver",
 *     depends={
 *         "App\DataMigration\Version",
 *         "App\DataMigration\Location",
 *         "App\DataMigration\EncounterMethod",
 *         "App\DataMigration\PokemonSpecies",
 *         "App\DataMigration\EncounterCondition"
 *     }
 * )
 */
class Encounter extends AbstractDoctrineDataMigration implements DataMigrationInterface
{
    private PokemonLookup $pokemonLookupHelper;

    /**
     * @inheritDoc
     */
    public function __construct(
        MigrationReferenceStoreInterface $referenceStore,
        PropertyAccessorInterface $propertyAccess
    ) {
        parent::__construct($referenceStore, $propertyAccess);
        $this->pokemonLookupHelper = new PokemonLookup($referenceStore);
    }

    /**
     * @inheritDoc
     *
     * @param DbalDestinationDriver $destinationDriver
     */
    public function configureDestination(DestinationDriverInterface $destinationDriver)
    {
        parent::configureDestination($destinationDriver);

        $destinationDriver->addTable(
            'encounter_encounter_condition_state',
            [
                'encounter_id',
                'encounter_condition_state_id',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function transform($sourceData, $destinationData)
    {
        $intFields = [
            'id',
            'chance',
        ];
        $sourceData = Normalizer::convertToInts($sourceData, $intFields);
        foreach ($sourceData as &$sourceDatum) {
            if ($sourceDatum === '') {
                $sourceDatum = null;
            }
        }
        $sourceData = Normalizer::removeNulls($sourceData);
        $encounterId = $sourceData['id'];
        $destinationData['encounter']['id'] = $sourceData['id'];
        $destinationData['encounter']['chance'] = $sourceData['chance'];
        if (isset($sourceData['note'])) {
            $destinationData['encounter']['note'] = $sourceData['note'];
        }

        // Position
        static $position = 1;
        $destinationData['encounter']['position'] = $position;
        $position++;

        // Version
        /** @var \App\Entity\Version $version */
        $version = $this->referenceStore->get(Version::class, ['identifier' => $sourceData['version']]);
        $versionGroup = $version->getVersionGroup();
        $destinationData['encounter']['version_id'] = $version->getId();

        // Location Area
        /** @var \App\Entity\Location $location */
        $location = $this->referenceStore->get(Location::class, ['identifier' => $sourceData['location']]);
        $location = $location->findChildByGrouping($versionGroup);
        $locationArea = null;
        // TODO: This can only find top-level areas
        foreach ($location->getAreas() as $checkLocationArea) {
            if ($checkLocationArea->getSlug() === $sourceData['area']) {
                $locationArea = $checkLocationArea;
                break;
            }
        }
        if (is_null($locationArea)) {
            throw new MigrationException(
                sprintf(
                    'Encounter %u occurs in location "%s", area "%s".  The area does not exist.',
                    $encounterId,
                    $location->getName(),
                    $sourceData['area']
                )
            );
        }
        $destinationData['encounter']['location_area_id'] = $locationArea->getId();

        // Method
        /** @var \App\Entity\EncounterMethod $encounterMethod */
        $encounterMethod = $this->referenceStore->get(EncounterMethod::class, ['identifier' => $sourceData['method']]);
        $destinationData['encounter']['method_id'] = $encounterMethod->getId();

        // Pokemon
        $pokemon = $this->pokemonLookupHelper->lookupPokemon(
            $versionGroup,
            $sourceData['species'],
            $sourceData['pokemon']
        );
        $destinationData['encounter']['pokemon_id'] = $pokemon->getId();

        // Level range
        if (isset($sourceData['level'])) {
            $levelRange = Range::fromString($sourceData['level']);
            $destinationData['encounter']['level_min'] = $levelRange->getMin();
            $destinationData['encounter']['level_max'] = $levelRange->getMax();
        }

        // Conditions
        if (isset($sourceData['conditions'])) {
            $conditions = explode(',', $sourceData['conditions']);
            foreach ($conditions as &$condition) {
                $conditionParts = explode('/', $condition, 2);
                $condition = $conditionParts[0];
                $stateIdentifier = str_replace($condition.'-', '', $conditionParts[1]);
                /** @var \App\Entity\EncounterCondition $condition */
                $condition = $this->referenceStore->get(EncounterCondition::class, ['identifier' => $condition]);
                $state = null;
                foreach ($condition->getStates() as $checkState) {
                    if ($checkState->getSlug() == $stateIdentifier) {
                        $state = $checkState;
                        break;
                    }
                }
                if (is_null($state)) {
                    throw new MigrationException(
                        sprintf(
                            'Encounter %u requires the state "%s".  The state does not exist.',
                            $encounterId,
                            $stateIdentifier
                        )
                    );
                }
                $destinationData['encounter_encounter_condition_state'][] = [
                    'encounter_id' => $encounterId,
                    'encounter_condition_state_id' => $state->getId(),
                ];
            }
        }

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return [
            'encounter' => [],
            'encounter_encounter_condition_state' => [],
        ];
    }

    /**
     * @inheritDoc
     */
    public function cleanup(): void
    {
        unset($this->pokemonLookupHelper);
    }

}
