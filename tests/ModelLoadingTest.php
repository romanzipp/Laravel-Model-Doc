<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use romanzipp\ModelDoc\Services\Objects\Model;

class ModelLoadingTest extends TestCase
{
    public function testValidModel()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/DummyModel.php')
        );

        self::assertInstanceOf(Model::class, $model);
    }

    public function testNotExtendingIlluminate()
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionMessage('Class does not extend Illuminate\Database\Eloquent\Model');

        new Model(
            $this->getFile(__DIR__ . '/Support/ClassNotExtendingIlluminateModel.php')
        );
    }

    public function testFileNotExisting()
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionMessage('File not found');

        new Model(
            $this->getFile(__DIR__ . '/Support/Foo.php')
        );
    }
}
