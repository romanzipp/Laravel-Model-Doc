<?php

namespace romanzipp\ModelDoc\Tests;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Tests\Support\Files\Model;

class ModelGeneratorCustomTagsTest extends TestCase
{
    public function testEmptyMixin()
    {
        config([
            'model-doc.custom_tags.mixins' => [],
        ]);

        $doc = $this->app->make(DocumentationGenerator::class)->generateModelDocBlock(new \romanzipp\ModelDoc\Services\Objects\Model(
            $this->getFile(__DIR__ . '/Support/ModelEmpty.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property int $id',
            ' */',
        ], $doc);
    }

    public function testSingleMixin()
    {
        config([
            'model-doc.custom_tags.mixins' => [
                EloquentModel::class,
            ],
        ]);

        $doc = $this->app->make(DocumentationGenerator::class)->generateModelDocBlock(new \romanzipp\ModelDoc\Services\Objects\Model(
            $this->getFile(__DIR__ . '/Support/ModelEmpty.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @mixin \Illuminate\Database\Eloquent\Model',
            ' *',
            ' * @property int $id',
            ' */',
        ], $doc);
    }

    public function testMultipleMixins()
    {
        config([
            'model-doc.custom_tags.mixins' => [
                EloquentModel::class,
                Model::class,
            ],
        ]);

        $doc = $this->app->make(DocumentationGenerator::class)->generateModelDocBlock(new \romanzipp\ModelDoc\Services\Objects\Model(
            $this->getFile(__DIR__ . '/Support/ModelEmpty.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @mixin \Illuminate\Database\Eloquent\Model',
            ' * @mixin \romanzipp\ModelDoc\Tests\Support\Files\Model',
            ' *',
            ' * @property int $id',
            ' */',
        ], $doc);
    }
}
