<?php

namespace romanzipp\ModelDoc\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Symfony\Component\Finder\SplFileInfo;

class TestCase extends BaseTestCase
{
    public function getFile(string $path): SplFileInfo
    {
        return new SplFileInfo(
            $path,
            '',
            basename($path)
        );
    }
}
