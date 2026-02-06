<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use romanzipp\ModelDoc\Tests\Support\Attributes\TestAttribute;

/**
 * @property int $column_integer
 */
#[TestAttribute,
    TestAttribute,]
class ModelWithExistingPhpDocAndAttributeMultiline extends EloquentModel
{
    protected $table = 'table_one';
}
