<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class GeneratorAccessorsTest extends TestCase
{
    public function testGeneratesAccessorProperties()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.attributes.enabled' => false,
            'model-doc.accessors.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelAccessors.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property mixed $untyped',
            ' * @property string $some_string',
            ' * @property int $some_int',
            ' * @property array $some_array',
            ' */',
        ], $doc);
    }
}
