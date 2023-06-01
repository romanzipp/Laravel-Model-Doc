<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelAttributeCast extends EloquentModel
{
    protected $table = 'table_one';

    public function setOnly(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value,
        );
    }

    public function untyped(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    public function someString(): Attribute
    {
        return Attribute::make(
            get: fn ($value): string => $value,
        );
    }

    public function someInt(): Attribute
    {
        return Attribute::make(
            get: fn ($value): int => $value,
        );
    }

    public function someArray(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): array => $value,
        );
    }

    public function someInstance(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): ClassNotExtendingIlluminateModel => $value,
        );
    }
}
