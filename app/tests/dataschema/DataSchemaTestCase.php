<?php

namespace App\Tests\dataschema;

use App\Tests\dataschema\Validator\MediaType\CommonMarkMediaType;
use App\Tests\dataschema\Validator\MediaType\MathMlMediaType;
use App\Tests\dataschema\Validator\MediaType\SvgMediaType;
use Ds\Map;
use Opis\JsonSchema\FilterContainer;
use Opis\JsonSchema\Loaders\File;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationError;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Base Test Case for schema validation
 */
abstract class DataSchemaTestCase extends TestCase
{
    protected const SCHEMA_BASE_URI = 'https://poketools.gamestuff.info/data/schema/';
    protected const BASE_DIR_SCHEMA = __DIR__.'/../../resources/schema/';
    protected const BASE_DIR_DATA = self::BASE_DIR_SCHEMA.'/../data';

    /** @var \Ds\Map Cache parsed schemas */
    static private Map $schemas;

    /** @var \Ds\Map Cache built validators */
    static private Map $validators;

    public static function setUpBeforeClass(): void
    {
        self::$schemas = new Map();
        self::$validators = new Map();
    }

    public static function tearDownAfterClass(): void
    {
        // Free memory
        self::$schemas->clear();
        self::$validators->clear();
    }

    /**
     * Assert the data follows the schema.
     *
     * @param string $name
     * @param array|object $data
     * @param string|null $context
     */
    protected function assertDataSchema(string $name, $data, ?string $context = null): void
    {
        if (!self::$schemas->hasKey($name)) {
            $schemaPath = realpath(self::BASE_DIR_SCHEMA.$name.'.json');
            self::$schemas->put($name, Schema::fromJsonString(file_get_contents($schemaPath)));
        }
        if (!self::$validators->hasKey($name)) {
            $loader = new File(self::SCHEMA_BASE_URI, [self::BASE_DIR_SCHEMA]);
            $validator = new Validator(null, $loader);
            $this->addMediaTypes($validator);
            $this->addFilters($validator);
            self::$validators->put($name, $validator);
        } else {
            $validator = self::$validators->get($name);
        }

        // Kludge to get data to be properly encapsulated (arrays vs objects)
        $data = json_decode(json_encode($data, JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION), false);

        $result = $validator->schemaValidation($data, self::$schemas->get($name), 1, $validator->getLoader());
        $this->assertTrue($result->isValid(), $this->buildSchemaErrorMessage($result->getErrors(), $context));
    }

    /**
     * @param Validator $validator
     */
    private function addMediaTypes(Validator $validator): void
    {
        $mediaTypes = $this->getMediaTypes();

        $mediaTypeContainer = $validator->getMediaType();
        foreach ($mediaTypes as $mime => $mediaType) {
            $mediaTypeContainer->add($mime, $mediaType);
        }
    }

    /**
     * @return array
     *   An array mapping content types to their MediaType objects
     */
    protected function getMediaTypes(): array
    {
        return [
            'text/markdown; variant=CommonMark' => new CommonMarkMediaType(),
            'application/mathml+xml' => new MathMlMediaType(),
            'image/svg+xml' => new SvgMediaType(),
        ];
    }

    /**
     * @param Validator $validator
     */
    private function addFilters(Validator $validator): void
    {
        $filters = $this->getFilters();

        $filterContainer = new FilterContainer();
        foreach ($filters as $dataType => $dataTypeFilters) {
            foreach ($dataTypeFilters as $name => $filter) {
                $filterContainer->add($dataType, $name, $filter);
            }
        }
        $validator->setFilters($filterContainer);
    }

    /**
     * @return array
     *   An multi-level array:
     *   - json data type (boolean, number, integer, string, null, array, object)
     *   - name: the name you will use in your schemas
     *   - the filter object that implements Opis\JsonSchema\IFilter
     */
    protected function getFilters(): array
    {
        return [];
    }

    /**
     * @param ValidationError[] $errors
     *
     * @param string|null $context
     *
     * @return string
     */
    private function buildSchemaErrorMessage(array $errors, ?string $context = null): string
    {
        $messages = [];
        foreach ($errors as $error) {
            $errorContext = $error->dataPointer();
            if ($context !== null) {
                array_unshift($errorContext, $context);
            }
            $messages[] = sprintf(
                "[%s]:\nCaused by: %s\nData: %s\nError:%s\n",
                implode('.', $errorContext),
                $error->keyword(),
                json_encode($error->data(), JSON_PRETTY_PRINT),
                json_encode($error->keywordArgs(), JSON_PRETTY_PRINT)
            );
            if ($error->subErrorsCount() > 0) {
                $messages[] = self::buildSchemaErrorMessage($error->subErrors(), $context);
            }
        }

        return "Data does not follow schema:\n".implode(str_repeat('-', 10)."\n", $messages);
    }

}
