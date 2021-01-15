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

    public function testRelations()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/SimpleRelationsModel.php')
        );

        $doc = (new DocumentationGenerator())->generateDocBlock($model);

        self::assertDocBlock([
            '/**',
            // BelongsTo
            ' * @property \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $belongsToRelation',
            ' * @property int|null $belongs_to_relations_count',
            // BelongsToMany
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $belongsToManyRelation',
            ' * @property int|null $belongs_to_many_relations_count',
            // HasOne
            ' * @property \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $hasOneRelation',
            ' * @property int|null $has_one_relations_count',
            // HasOneThrough
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasOneThroughRelation',
            ' * @property int|null $has_one_through_relations_count',
            // HasMany
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasManyRelation',
            ' * @property int|null $has_many_relations_count',
            // HasManyThrough
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasManyThroughRelation',
            ' * @property int|null $has_many_through_relations_count',
            ' */',
        ], $doc);
    }
}
