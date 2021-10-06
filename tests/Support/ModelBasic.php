<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelBasic extends EloquentModel
{
    protected $table = 'table_one';
}
