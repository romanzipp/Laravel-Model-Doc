<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use romanzipp\ModelDoc\Tests\Support\Factories\BasicFactory;

class ModelFactoryBasic extends EloquentModel
{
    use HasFactory;

    protected $table = 'table_one';

    protected static function newFactory()
    {
        return new BasicFactory();
    }
}
