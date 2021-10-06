<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class GeneratorQueryScopesTest extends TestCase
{
    public function testMorphRelations()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.relations.counts.enabled' => false,
            'model-doc.scopes.enabled' => true,
        ]);

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelQueryScopes.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereNoParameters()',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleParameter($id)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameter(int $id)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterAllowsNull(?int $id)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterComplex(\romanzipp\ModelDoc\Tests\Support\ModelBasic $user)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterComplexAllowsNull(?\romanzipp\ModelDoc\Tests\Support\ModelBasic $user)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParameters($id, $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParametersTypeHinted(int $id, string $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParametersTypeHintedAllowsNull(?int $id, ?string $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleTypedParametersComplex(\romanzipp\ModelDoc\Tests\Support\ModelBasic $user, \romanzipp\ModelDoc\Tests\Support\ModelBasic $otherUser)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleTypedParametersComplexAllowsNull(?\romanzipp\ModelDoc\Tests\Support\ModelBasic $user, ?\romanzipp\ModelDoc\Tests\Support\ModelBasic $otherUser)',
            ' */',
        ], $doc);
    }

    public function testMorphRelationsIgnoreSpecific()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.relations.counts.enabled' => false,
            'model-doc.scopes.enabled' => true,
            'model-doc.scopes.ignore' => ['whereSingleTypedParameter'],
        ]);

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelQueryScopes.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereNoParameters()',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleParameter($id)',
            // ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameter(int $id)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterAllowsNull(?int $id)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterComplex(\romanzipp\ModelDoc\Tests\Support\ModelBasic $user)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterComplexAllowsNull(?\romanzipp\ModelDoc\Tests\Support\ModelBasic $user)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParameters($id, $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParametersTypeHinted(int $id, string $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParametersTypeHintedAllowsNull(?int $id, ?string $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleTypedParametersComplex(\romanzipp\ModelDoc\Tests\Support\ModelBasic $user, \romanzipp\ModelDoc\Tests\Support\ModelBasic $otherUser)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleTypedParametersComplexAllowsNull(?\romanzipp\ModelDoc\Tests\Support\ModelBasic $user, ?\romanzipp\ModelDoc\Tests\Support\ModelBasic $otherUser)',
            ' */',
        ], $doc);
    }

    public function testMorphRelationsIgnoreWildcard()
    {
        config([
            'model-doc.relations.enabled' => false,
            'model-doc.relations.counts.enabled' => false,
            'model-doc.scopes.enabled' => true,
            'model-doc.scopes.ignore' => ['/whereSingleTypedParameter.*/'],
        ]);

        $doc = (new DocumentationGenerator())->generateDocBlock(new Model(
            $this->getFile(__DIR__ . '/Support/ModelQueryScopes.php')
        ));

        self::assertDocBlock([
            '/**',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereNoParameters()',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleParameter($id)',
            // ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameter(int $id)',
            // ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterAllowsNull(?int $id)',
            // ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterComplex(\romanzipp\ModelDoc\Tests\Support\ModelBasic $user)',
            // ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereSingleTypedParameterComplexAllowsNull(?\romanzipp\ModelDoc\Tests\Support\ModelBasic $user)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParameters($id, $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParametersTypeHinted(int $id, string $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleParametersTypeHintedAllowsNull(?int $id, ?string $name)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleTypedParametersComplex(\romanzipp\ModelDoc\Tests\Support\ModelBasic $user, \romanzipp\ModelDoc\Tests\Support\ModelBasic $otherUser)',
            ' * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\ModelDoc\Tests\Support\ModelQueryScopes whereMultipleTypedParametersComplexAllowsNull(?\romanzipp\ModelDoc\Tests\Support\ModelBasic $user, ?\romanzipp\ModelDoc\Tests\Support\ModelBasic $otherUser)',
            ' */',
        ], $doc);
    }
}
