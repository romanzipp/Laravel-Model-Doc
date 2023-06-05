<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelParent extends EloquentModel
{
    protected $table = 'table_one';

    protected function parentDefinition(): Attribute
    {
        return Attribute::make(
            get: fn ($value): string => $value,
        );
    }
}
