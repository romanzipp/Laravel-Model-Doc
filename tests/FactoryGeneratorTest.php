<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class FactoryGeneratorTest extends TestCase
{
    public function testGenerateFactory()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/ModelFactoryBasic.php')
        );

        $doc = (new DocumentationGenerator())->generateFactoryDocBlock($model->getFactory());

        self::assertDocBlock([
            '/**',
            ' * @method romanzipp\ModelDoc\Tests\Support\ModelFactoryBasic created(array $attributes = [])',
            ' * @method romanzipp\ModelDoc\Tests\Support\ModelFactoryBasic make(array $attributes = [])',
            ' */',
        ], $doc);
    }
}
