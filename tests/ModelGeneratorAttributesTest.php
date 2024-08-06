<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class ModelGeneratorAttributesTest extends TestCase
{
    public function testBasicAttributes()
    {
        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelBasic.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property int $column_integer',
            ' * @property int|null $column_integer_nullable',
            ' * @property string $column_string',
            ' * @property string|null $column_string_nullable',
            ' * @property bool $column_boolean',
            ' * @property bool|null $column_boolean_nullable',
            ' */',
        ], $doc);
    }

    public function testExtendedAttributes()
    {
        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelExtended.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property int $column_tiny_integer',
            ' * @property int $column_small_integer',
            ' * @property int $column_medium_integer',
            ' * @property int $column_big_integer',
            ' * @property string $column_char',
            ' * @property string $column_string',
            ' * @property string $column_tiny_text',
            ' * @property string $column_text',
            ' * @property string $column_medium_text',
            ' * @property string $column_long_text',
            ' * @property string $column_json',
            ' * @property string $column_jsonb',
            ' * @property int $column_year',
            ' * @property string $column_binary',
            ' * @property string $column_uuid',
            ' * @property string $column_ulid',
            ' * @property string $column_ip_address',
            ' * @property string $column_mac_address',
            ' */',
        ], $doc);
    }

    public function testAttributesDisabled()
    {
        config([
            'model-doc.attributes.enabled' => false,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelBasic.php')
        ));

        self::assertDocBlock([
            '/**',
            ' */',
        ], $doc);
    }

    public function testAttributesCasted()
    {
        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelBasicWithCasts.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property string $column_integer',
            ' * @property string|null $column_integer_nullable',
            ' * @property \romanzipp\ModelDoc\Tests\Support\Files\CastedType $column_string',
            ' * @property \romanzipp\ModelDoc\Tests\Support\Files\CastedType|null $column_string_nullable',
            ' * @property bool $column_boolean',
            ' * @property bool|null $column_boolean_nullable',
            ' */',
        ], $doc);
    }

    public function testSpecialModelTypes()
    {
        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelSpecialAttributes.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property string $column_enum',
            ' */',
        ], $doc);
    }

    public function testCustomTypeMappings()
    {
        config([
            'model-doc.attributes.custom_mappings' => [
                'integer' => Support\Files\Model::class,
            ],
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelBasic.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property \\' . Support\Files\Model::class . ' $column_integer',
            ' * @property \\' . Support\Files\Model::class . '|null $column_integer_nullable',
            ' * @property string $column_string',
            ' * @property string|null $column_string_nullable',
            ' * @property bool $column_boolean',
            ' * @property bool|null $column_boolean_nullable',
            ' */',
        ], $doc);

        config([
            'model-doc.attributes.custom_mappings' => [],
        ]);
    }
}
