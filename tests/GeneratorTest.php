<?php

namespace romanzipp\ModelDoc\Tests;

use romanzipp\ModelDoc\Services\DocumentationGenerator;
use romanzipp\ModelDoc\Services\Objects\Model;

class GeneratorTest extends TestCase
{
    public function testBasic()
    {
        $model = new Model(
            $this->getFile(__DIR__ . '/Support/BasicModel.php')
        );

        $generator = new DocumentationGenerator();
        $generator->generate($model);
    }
}
