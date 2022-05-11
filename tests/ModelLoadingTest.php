<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;
use romanzipp\ModelDoc\Tests\Support\ModelNoTable;

class ModelLoadingTest extends TestCase
{
    public function testGeneratorCustomPath()
    {
        DocumentationGenerator::usePath(fn () => __DIR__ . '/Support/Files');

        $generator = new DocumentationGenerator();
        $models = iterator_to_array($generator->collectModels());

        self::assertCount(1, $models);
        self::assertSame('romanzipp\\ModelDoc\\Tests\\Support\\Files\\Model', $models[0]->getReflectionClass()->getName());
    }

    public function testValidModel()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/ModelNoTable.php')
        );

        self::assertFalse($model->isIgnored());
        self::assertInstanceOf(Model::class, $model);
    }

    public function testModelIsIgnored()
    {
        config(['model-doc.ignore' => [ModelNoTable::class]]);

        $model = new Model(
            $this->getFile(__DIR__ . '/Support/ModelNoTable.php')
        );

        self::assertTrue($model->isIgnored());
        self::assertInstanceOf(Model::class, $model);
    }

    public function testNotExtendingIlluminate()
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionMessage('Class does not extend Illuminate\Database\Eloquent\Model');

        new Model(
            $this->getFile(__DIR__ . '/Support/ClassNotExtendingIlluminateModel.php')
        );
    }

    public function testFileNotExisting()
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionMessage('File not found');

        new Model(
            $this->getFile(__DIR__ . '/Support/Foo.php')
        );
    }
}
