<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use romanzipp\ModelDoc\Tests\Support\Files\CastedType;

class ModelBasicWithCasts extends EloquentModel
{
    protected $table = 'table_one';

    protected $casts = [
        'column_integer' => 'string',
        'column_integer_nullable' => 'string',
        'column_string' => CastedType::class,
        'column_string_nullable' => '\romanzipp\ModelDoc\Tests\Support\Files\CastedType',
    ];
}
