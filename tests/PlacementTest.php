<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

final class PlacementTest extends TestCase
{
    public function testGenerateForModelWithExistingPhpDoc()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
        ]);

        self::backup('ModelWithExistingPhpDoc');

        $this->app->make(DocumentationGenerator::class)->generate(new Model(
            $this->getFile(__DIR__ . '/Support/ModelWithExistingPhpDoc.php')
        ));

        $contents = explode(
            PHP_EOL,
            file_get_contents(__DIR__ . '/Support/ModelWithExistingPhpDoc.php')
        );

        self::assertSame([
            '<?php',
            '',
            'namespace romanzipp\ModelDoc\Tests\Support;',
            '',
            'use Illuminate\Database\Eloquent\Model as EloquentModel;',
            '',
            '/**',
            ' * @property int $column_integer',
            ' * @property int|null $column_integer_nullable',
            ' * @property string $column_string',
            ' * @property string|null $column_string_nullable',
            ' * @property int $column_boolean',
            ' * @property int|null $column_boolean_nullable',
            ' */',
            'class ModelWithExistingPhpDoc extends EloquentModel',
            '{',
            '    protected $table = \'table_one\';',
            '}',
            '',
        ], $contents);

        self::restore('ModelWithExistingPhpDoc');
    }

    public function testGenerateForModelWithExistingPhpDocAndAttribute()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
        ]);

        self::backup('ModelWithExistingPhpDocAndAttribute');

        $this->app->make(DocumentationGenerator::class)->generate(new Model(
            $this->getFile(__DIR__ . '/Support/ModelWithExistingPhpDocAndAttribute.php')
        ));

        $contents = explode(
            PHP_EOL,
            file_get_contents(__DIR__ . '/Support/ModelWithExistingPhpDocAndAttribute.php')
        );

        self::assertSame([
            '<?php',
            '',
            'namespace romanzipp\ModelDoc\Tests\Support;',
            '',
            'use Illuminate\Database\Eloquent\Model as EloquentModel;',
            'use romanzipp\ModelDoc\Tests\Support\Attributes\TestAttribute;',
            '',
            '/**',
            ' * @property int $column_integer',
            ' * @property int|null $column_integer_nullable',
            ' * @property string $column_string',
            ' * @property string|null $column_string_nullable',
            ' * @property int $column_boolean',
            ' * @property int|null $column_boolean_nullable',
            ' */',
            '#[TestAttribute]',
            'class ModelWithExistingPhpDocAndAttribute extends EloquentModel',
            '{',
            '    protected $table = \'table_one\';',
            '}',
            '',
        ], $contents);

        self::restore('ModelWithExistingPhpDocAndAttribute');
    }

    public function testGenerateForModelWithExistingPhpDocAndAttributeMultiline()
    {
        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
        ]);

        self::backup('ModelWithExistingPhpDocAndAttributeMultiline');

        $this->app->make(DocumentationGenerator::class)->generate(new Model(
            $this->getFile(__DIR__ . '/Support/ModelWithExistingPhpDocAndAttributeMultiline.php')
        ));

        $contents = explode(
            PHP_EOL,
            file_get_contents(__DIR__ . '/Support/ModelWithExistingPhpDocAndAttributeMultiline.php')
        );

        self::assertSame([
            '<?php',
            '',
            'namespace romanzipp\ModelDoc\Tests\Support;',
            '',
            'use Illuminate\Database\Eloquent\Model as EloquentModel;',
            'use romanzipp\ModelDoc\Tests\Support\Attributes\TestAttribute;',
            '',
            '/**',
            ' * @property int $column_integer',
            ' * @property int|null $column_integer_nullable',
            ' * @property string $column_string',
            ' * @property string|null $column_string_nullable',
            ' * @property int $column_boolean',
            ' * @property int|null $column_boolean_nullable',
            ' */',
            '#[',
            '    TestAttribute,',
            '    TestAttribute,',
            ']',
            'class ModelWithExistingPhpDocAndAttributeMultiline extends EloquentModel',
            '{',
            '    protected $table = \'table_one\';',
            '}',
            '',
        ], $contents);

        self::restore('ModelWithExistingPhpDocAndAttributeMultiline');
    }

    private static function backup(string $modelName): void
    {
        @unlink(__DIR__ . '/Support/_' . $modelName . '.php');

        copy(
            __DIR__ . '/Support/' . $modelName . '.php',
            __DIR__ . '/Support/_' . $modelName . '.php',
        );
    }

    private static function restore(string $modelName): void
    {
        unlink(__DIR__ . '/Support/' . $modelName . '.php') ?? throw new \RuntimeException('Failed to delete file (restore 1)');

        copy(
            __DIR__ . '/Support/_' . $modelName . '.php',
            __DIR__ . '/Support/' . $modelName . '.php',
        );

        unlink(__DIR__ . '/Support/_' . $modelName . '.php') ?? throw new \RuntimeException('Failed to delete file (restore 2)');
    }
}
