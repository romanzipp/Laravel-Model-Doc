<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelAccessorsDuplicateAttribute extends EloquentModel
{
    protected $table = 'table_one';

    public function getColumnStringAttribute(): int
    {
        return 1;
    }
}
