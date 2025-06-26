<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property string $foo
 */
class ModelWithExistingPhpDoc extends EloquentModel
{
    protected $table = 'table_one';
}
