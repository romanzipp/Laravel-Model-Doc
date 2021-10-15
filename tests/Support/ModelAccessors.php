<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelAccessors extends EloquentModel
{
    protected $table = 'table_one';

    public function getUntypedAttribute()
    {
        return '';
    }

    public function getSomeStringAttribute(): string
    {
        return '';
    }

    public function getSomeIntAttribute(): int
    {
        return 1;
    }

    public function getSomeArrayAttribute(): array
    {
        return [];
    }
}
