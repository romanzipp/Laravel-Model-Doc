<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException;
use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class ModelGeneratorRelationsTest extends TestCase
{
    public function testRelations()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelSimpleRelations.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property-read \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $belongsToRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $belongsToManyRelation',
            ' * @property-read \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $hasOneRelation',
            ' * @property-read \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $hasOneThroughRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasManyRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasManyThroughRelation',
            ' *',
            ' * @property int|null $belongs_to_relations_count',
            ' * @property int|null $belongs_to_many_relations_count',
            ' * @property int|null $has_one_relations_count',
            ' * @property int|null $has_one_through_relations_count',
            ' * @property int|null $has_many_relations_count',
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

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelSimpleRelations.php')
        ));

        self::assertDocBlock([
            '/**',
            // BelongsTo
            ' * @property-read \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $belongsToRelation',
            // BelongsToMany
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $belongsToManyRelation',
            // HasOne
            ' * @property-read \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $hasOneRelation',
            // HasOneThrough
            ' * @property-read \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $hasOneThroughRelation',
            // HasMany
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasManyRelation',
            // HasManyThrough
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $hasManyThroughRelation',
            ' */',
        ], $doc);
    }

    public function testAllRelationsDisabled()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.relations.counts.enabled' => false,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelSimpleRelations.php')
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

        (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelSimpleRelations.php')
        ));
    }

    public function testCorrectPluralCounts()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelPluralRelations.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $votesUp',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $votesDown',
            ' *',
            ' * @property int|null $votes_up_count',
            ' * @property int|null $votes_down_count',
            ' */',
        ], $doc);
    }

    public function testMorphRelations()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelMorphRelations.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property-read \Illuminate\Database\Eloquent\Model|null $morphToRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Model|null $morphToRelationWithType',
            ' * @property-read \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $morphOneRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphManyRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphToManyRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphedByManyRelation',
            ' *',
            ' * @property int|null $morph_to_relations_count',
            ' * @property int|null $morph_to_relation_with_types_count',
            ' * @property int|null $morph_one_relations_count',
            ' * @property int|null $morph_many_relations_count',
            ' * @property int|null $morph_to_many_relations_count',
            ' * @property int|null $morphed_by_many_relations_count',
            ' */',
        ], $doc);
    }

    public function testMorphRelationsWithoutCount()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => false,
        ]);

        $doc = (new DocumentationGenerator())->generateModelDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelMorphRelations.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property-read \Illuminate\Database\Eloquent\Model|null $morphToRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Model|null $morphToRelationWithType',
            ' * @property-read \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $morphOneRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphManyRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphToManyRelation',
            ' * @property-read \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphedByManyRelation',
            ' */',
        ], $doc);
    }
}
