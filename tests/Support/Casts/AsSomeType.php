<?php

namespace romanzipp\ModelDoc\Tests\Support\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class AsSomeType implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): SomeType
    {
        return new SomeType(value: $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof SomeType) {
            return $value->value;
        }

        return null;
    }
}
