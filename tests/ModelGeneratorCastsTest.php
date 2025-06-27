<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class ModelGeneratorCastsTest extends TestCase
{
    public function testGeneratePropertiesForCasts()
    {
        config([
            'model-doc.casts.enabled' => true,
        ]);

        $doc = $this->app->make(DocumentationGenerator::class)->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelCustomCast.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property int $column_integer',
            ' * @property int|null $column_integer_nullable',
            ' * @property \romanzipp\ModelDoc\Tests\Support\Casts\SomeType $column_string',
            ' * @property string|null $column_string_nullable',
            ' * @property int $column_boolean',
            ' * @property int|null $column_boolean_nullable',
            ' */',
        ], $doc);
    }
}
