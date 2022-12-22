<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelEmpty extends EloquentModel
{
    protected $table = 'table_empty';
}
