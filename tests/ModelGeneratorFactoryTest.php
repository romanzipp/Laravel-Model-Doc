<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class ModelGeneratorFactoryTest extends TestCase
{
    public function testDisabled()
    {
        config([
            'model-doc.attributes.enabled' => false,
            'model-doc.relations.enabled' => false,
            'model-doc.relations.counts.enabled' => false,
            'model-doc.factories.enabled' => false,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelFactoryBasic.php')
        ));

        self::assertDocBlock([
            '/**',
            ' */',
        ], $doc);
    }

    public function testFactoryMethodTag()
    {
        config([
            'model-doc.attributes.enabled' => false,
            'model-doc.relations.enabled' => false,
            'model-doc.relations.counts.enabled' => false,
            'model-doc.factories.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelFactoryBasic.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @method static \romanzipp\ModelDoc\Tests\Support\Factories\BasicFactory<self> factory($count = null, $state = [])',
            ' */',
        ], $doc);
    }
}
