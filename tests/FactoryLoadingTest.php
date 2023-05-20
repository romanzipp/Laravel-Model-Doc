<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\Objects\Factory;
use romanzipp\ModelDoc\Services\Objects\Model;

class FactoryLoadingTest extends TestCase
{
    public function testNoFactoryForModel()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/ModelBasic.php')
        );

        self::assertNull($model->getFactory());
    }

    public function testGetFactoryFromModel()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/ModelFactoryBasic.php')
        );

        self::assertInstanceOf(Factory::class, $model->getFactory());
    }
}
