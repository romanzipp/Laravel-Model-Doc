<?php

namespace romanzipp\ModelDoc\Tests\Support;

use romanzipp\ModelDoc\Tests\Support\Casts\AsSomeType;

class ModelCustomCast extends ModelParent
{
    protected $casts = [
        'column_string' => AsSomeType::class,
    ];
}
