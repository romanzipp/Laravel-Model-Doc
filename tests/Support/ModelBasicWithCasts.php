<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelBasicWithCasts extends EloquentModel
{
    protected $table = 'table_one';

    protected $casts = [
        'column_integer' => 'string',
        'column_integer_nullable' => 'string',
    ];
}
