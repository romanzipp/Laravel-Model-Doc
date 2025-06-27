<?php

namespace romanzipp\ModelDoc\Tests\Support;

use romanzipp\ModelDoc\Tests\Support\Casts\AsInteger;
use romanzipp\ModelDoc\Tests\Support\Casts\AsSomeType;
use romanzipp\ModelDoc\Tests\Support\Casts\AsSomeTypeNullable;

class ModelCustomCast extends ModelParent
{
    protected $casts = [
        'column_string' => AsSomeType::class,
        'column_string_nullable' => AsSomeTypeNullable::class,
        'column_integer' => AsInteger::class,
    ];
}
