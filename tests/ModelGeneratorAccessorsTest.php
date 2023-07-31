<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class ModelGeneratorAccessorsTest extends TestCase
{
    public function testGeneratesAccessorProperties()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.attributes.enabled' => false,
            'model-doc.accessors.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelAccessors.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property mixed $untyped',
            ' * @property string $some_string',
            ' * @property int $some_int',
            ' * @property array $some_array',
            ' * @property \romanzipp\ModelDoc\Tests\Support\ClassNotExtendingIlluminateModel $some_instance',
            ' */',
        ], $doc);
    }

    public function testDontGenerateAttributeTagIfAccessorExists()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.attributes.enabled' => true,
            'model-doc.accessors.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelAccessorsDuplicateAttribute.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property int $column_integer',
            ' * @property int|null $column_integer_nullable',
            ' * @property string|null $column_string_nullable',
            ' * @property bool $column_boolean',
            ' * @property bool|null $column_boolean_nullable',
            ' * @property int $column_string',
            ' */',
        ], $doc);
    }

    public function testAttributeAccessor()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.attributes.enabled' => false,
            'model-doc.accessors.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelAttributeCast.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property mixed $getOnly',
            ' * @property mixed $get_only',
            ' * @property mixed $untyped',
            ' * @property string $someString',
            ' * @property string $some_string',
            ' * @property int $someInt',
            ' * @property int $some_int',
            ' * @property array $someArray',
            ' * @property array $some_array',
            ' * @property \romanzipp\ModelDoc\Tests\Support\ClassNotExtendingIlluminateModel $someInstance',
            ' * @property \romanzipp\ModelDoc\Tests\Support\ClassNotExtendingIlluminateModel $some_instance',
            ' * @property string $parentDefinition',
            ' * @property string $parent_definition',
            ' */',
        ], $doc);
    }
}
