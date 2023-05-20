<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Factory;
use romanzipp\ModelDoc\Services\Objects\Model;

class FactoryGeneratorTest extends TestCase
{
    public function testNoFactoryForModel()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/ModelBasic.php')
        );

        self::assertNull($model->getFactory());
    }

    public function testGetFactoryFromModel()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/ModelFactoryBasic.php')
        );

        self::assertInstanceOf(Factory::class, $model->getFactory());
    }

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
