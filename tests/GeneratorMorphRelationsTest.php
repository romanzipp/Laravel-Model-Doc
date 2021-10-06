<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class GeneratorMorphRelationsTest extends TestCase
{
    public function testMorphRelations()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelMorphRelations.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property \Illuminate\Database\Eloquent\Model|null $morphToRelation',
            ' * @property int|null $morph_to_relations_count',
            ' * @property \Illuminate\Database\Eloquent\Model|null $morphToRelationWithType',
            ' * @property int|null $morph_to_relation_with_types_count',
            ' * @property \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $morphOneRelation',
            ' * @property int|null $morph_one_relations_count',
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphManyRelation',
            ' * @property int|null $morph_many_relations_count',
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphToManyRelation',
            ' * @property int|null $morph_to_many_relations_count',
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphedByManyRelation',
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

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelMorphRelations.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @property \Illuminate\Database\Eloquent\Model|null $morphToRelation',
            ' * @property \Illuminate\Database\Eloquent\Model|null $morphToRelationWithType',
            ' * @property \romanzipp\ModelDoc\Tests\Support\Related\RelatedModel|null $morphOneRelation',
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphManyRelation',
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphToManyRelation',
            ' * @property \Illuminate\Database\Eloquent\Collection|\romanzipp\ModelDoc\Tests\Support\Related\RelatedModel[] $morphedByManyRelation',
            ' */',
        ], $doc);
    }
}
