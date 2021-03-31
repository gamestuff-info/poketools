<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\RangeFilter;
use App\Tests\dataschema\Filter\TypeInVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Move
 *
 * @group data
 * @group move
 * @coversNothing
 */
class MoveTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('move', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('move');
    }

    /**
     * Test required data is present based on contest
     *
     * @dataProvider dataProvider
     */
    public function testRequiredData(array $data): void
    {
        // Contest data
        // version group => list of required fields
        $contestVersionGroups = [
            'ruby-sapphire' => ['contest_type', 'contest_effect'],
            'emerald' => ['contest_type', 'contest_effect'],
            'diamond-pearl' => ['contest_type', 'super_contest_effect'],
            'platinum' => ['contest_type', 'super_contest_effect'],
            'omega-ruby-alpha-sapphire' => ['contest_type', 'contest_effect'],
        ];
        // Needed so PHPUnit doesn't complain when testing a move that does not appear in the above version groups.
        $hasContestData = false;
        foreach ($contestVersionGroups as $versionGroup => $requiredFields) {
            if (!isset($data[$versionGroup])) {
                continue;
            }
            $hasContestData = true;
            foreach ($requiredFields as $field) {
                $this->assertArrayHasKey(
                    $field,
                    $data[$versionGroup],
                    sprintf('Missing %s field in version group %s', $field, $versionGroup)
                );
                $this->assertNotNull(
                    $data[$versionGroup][$field],
                    sprintf('Null value for field %s in version group %s', $field, $versionGroup)
                );
            }
        }
        if (!$hasContestData) {
            // If a move does not appear in a version group with contests, no assertions have been performed.
            // This is the only way to allow a test that only sometimes performs an assertion.
            $this->assertFalse($hasContestData);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'ailmentIdentifier' => new CsvIdentifierExists('move_ailment'),
                'moveFlagIdentifier' => new CsvIdentifierExists('move_flag'),
                'moveCategoryIdentifier' => new CsvIdentifierExists('move_category'),
                'range' => new RangeFilter(),
                'statIdentifier' => new CsvIdentifierExists('stat'),
                'typeIdentifier' => new CsvIdentifierExists('type'),
                'typeInVersionGroup' => new TypeInVersionGroup(),
                'moveTargetIdentifier' => new CsvIdentifierExists('move_target'),
                'moveDamageClassIdentifier' => new CsvIdentifierExists('move_damage_class'),
                'contestTypeIdentifier' => new CsvIdentifierExists('contest_type'),
                'moveIdentifier' => new YamlIdentifierExists('move'),
                'moveInVersionGroup' => new EntityHasVersionGroup('move'),
            ],
            'integer' => [
                'moveEffectId' => new YamlIdentifierExists('move_effect'),
                'moveEffectInVersionGroup' => new EntityHasVersionGroup('move_effect'),
                'contestEffectId' => new YamlIdentifierExists('contest_effect'),
                'superContestEffectId' => new CsvIdentifierExists('super_contest_effect', 'id'),
            ],
        ];
    }

}
