<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelExtended extends EloquentModel
{
    protected $table = 'table_extended';
}
