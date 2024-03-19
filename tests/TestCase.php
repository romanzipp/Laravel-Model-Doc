<?php

namespace romanzipp\ModelDoc\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use phpowermove\docblock\Docblock;
use Symfony\Component\Finder\SplFileInfo;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
            'model-doc.attributes.enabled' => true,
            'model-doc.tag_sorting' => [],
        ]);

        $this->setupDatabase($this->app);
    }

    protected function getFile(string $path): SplFileInfo
    {
        return new SplFileInfo(
            $path,
            '',
            basename($path)
        );
    }

    protected function setupDatabase(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->dropAllTables();

        $app['db']->connection()->getSchemaBuilder()->create('table_one', function (Blueprint $table) {
            $table->integer('column_integer');
            $table->integer('column_integer_nullable')->nullable();

            $table->string('column_string');
            $table->string('column_string_nullable')->nullable();

            $table->boolean('column_boolean');
            $table->boolean('column_boolean_nullable')->nullable();
        });

        $app['db']->connection()->getSchemaBuilder()->create('table_extended', function (Blueprint $table) {
            $table->tinyInteger('column_tiny_integer');
            $table->smallInteger('column_small_integer');
            $table->mediumInteger('column_medium_integer');
            $table->bigInteger('column_big_integer');

            $table->char('column_char');
            $table->string('column_string');
            $table->tinyText('column_tiny_text');
            $table->text('column_text');
            $table->mediumText('column_medium_text');
            $table->longText('column_long_text');

            $table->json('column_json');
            $table->jsonb('column_jsonb');
            $table->year('column_year');
            $table->binary('column_binary');
            $table->uuid('column_uuid');
            $table->ulid('column_ulid');
            $table->ipAddress('column_ip_address');
            $table->macAddress('column_mac_address');
        });

        $app['db']->connection()->getSchemaBuilder()->create('table_special', function (Blueprint $table) {
            $table->enum('column_enum', ['one', 'two']);
        });

        $app['db']->connection()->getSchemaBuilder()->create('table_empty', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    protected static function assertDocBlock(array $expected, Docblock $actual): void
    {
        self::assertSame(implode(PHP_EOL, $expected), $actual->toString());

        $actualLines = explode(PHP_EOL, $actual->toString());

        foreach ($expected as $index => $line) {
            self::assertSame($line, $actualLines[$index]);
        }
    }
}
