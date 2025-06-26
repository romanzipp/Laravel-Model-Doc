<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use romanzipp\ModelDoc\Tests\Support\Attributes\TestAttribute;

/**
 * @property string $foo
 */
#[TestAttribute]
class ModelWithExistingPhpDocAndAttribute extends EloquentModel
{
    protected $table = 'table_one';
}
