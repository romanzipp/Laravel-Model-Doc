<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException;
use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class GeneratorRelationsTest extends TestCase
{
    public function testRelations()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/SimpleRelationsModel.php')
        ));

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

    public function testRelationsWithoutCounts()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => false,
        ]);

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/SimpleRelationsModel.php')
        ));

        self::assertDocBlock([
            '/**',
            // BelongsTo
            ' * @property \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $belongsToRelation',
            // BelongsToMany
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $belongsToManyRelation',
            // HasOne
            ' * @property \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $hasOneRelation',
            // HasOneThrough
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasOneThroughRelation',
            // HasMany
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasManyRelation',
            // HasManyThrough
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasManyThroughRelation',
            ' */',
        ], $doc);
    }

    public function testAllRelationsDisabled()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.relations.counts.enabled' => false,
        ]);

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/SimpleRelationsModel.php')
        ));

        self::assertDocBlock([
            '/**',
            ' */',
        ], $doc);
    }

    public function testAllRelationsDisabledThrowException()
    {
        $this->expectException(ModelDocumentationFailedException::class);

        config([
            'model-doc.relations.enabled' => false,
            'model-doc.relations.counts.enabled' => false,
            'model-doc.fail_when_empty' => true,
        ]);

        (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/SimpleRelationsModel.php')
        ));
    }
}
