<?php

namespace romanzipp\ModelDoc\Tests;

use gossi\docblock\Docblock;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Symfony\Component\Finder\SplFileInfo;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

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
        $app['db']->connection()->getSchemaBuilder()->create('table_one', function (Blueprint $table) {
            $table->integer('column_integer');
            $table->integer('column_integer_nullable')->nullable();

            $table->string('column_string');
            $table->string('column_string_nullable')->nullable();

            $table->boolean('column_boolean');
            $table->boolean('column_boolean_nullable')->nullable();
        });
    }

    protected static function assertDocBlock(array $expected, Docblock $actual): void
    {
        self::assertSame(implode(PHP_EOL, $expected), $actual->toString());
    }
}
