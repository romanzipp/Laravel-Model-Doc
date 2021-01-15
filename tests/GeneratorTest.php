<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class GeneratorTest extends TestCase
{
    public function testBasic()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/BasicModel.php')
        );

        $doc = (new DocumentationGenerator())->generateDocBlock($model);

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
}
