<?php

namespace romanzipp\ModelDoc\Tests;

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

    protected function setupDatabase(Application $app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('table_one', function (Blueprint $table) {
            $table->integer('integer')->autoIncrement();
            $table->integer('string');
        });
    }
}
