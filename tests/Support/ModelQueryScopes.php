<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelQueryScopes extends EloquentModel
{
    public function scopeWhereNoParameters(Builder $builder)
    {
        $builder->where('enabled', true);
    }

    public function scopeWhereSingleParameter(Builder $builder, $id)
    {
        $builder->where('id', $id);
    }

    public function scopeWhereSingleTypedParameter(Builder $builder, int $id)
    {
        $builder->where('id', $id);
    }

    public function scopeWhereSingleTypedParameterAllowsNull(Builder $builder, ?int $id)
    {
        $builder->where('id', $id);
    }

    public function scopeWhereSingleTypedParameterComplex(Builder $builder, ModelBasic $user)
    {
        $builder->where('id', $user->id);
    }

    public function scopeWhereSingleTypedParameterComplexAllowsNull(Builder $builder, ?ModelBasic $user)
    {
        $builder->where('id', $user->id);
    }

    public function scopeWhereMultipleParameters(Builder $builder, $id, $name)
    {
        $builder->where('id', $id)->where('name', $name);
    }

    public function scopeWhereMultipleParametersTypeHinted(Builder $builder, int $id, string $name)
    {
        $builder->where('id', $id)->where('name', $name);
    }

    public function scopeWhereMultipleParametersTypeHintedAllowsNull(Builder $builder, ?int $id, ?string $name)
    {
        $builder->where('id', $id)->where('name', $name);
    }

    public function scopeWhereMultipleTypedParametersComplex(Builder $builder, ModelBasic $user, ModelBasic $otherUser)
    {
        $builder->where('id', $user->id)->where('id', $otherUser->id);
    }

    public function scopeWhereMultipleTypedParametersComplexAllowsNull(Builder $builder, ?ModelBasic $user, ?ModelBasic $otherUser)
    {
        $builder->where('id', $user->id)->where('id', $otherUser->id);
    }

    public function scopeWhereParameterDefaultInt(Builder $builder, int $id = 69)
    {
        $builder->where('id', $id);
    }

    public function scopeWhereParameterDefaultFloat(Builder $builder, float $id = 69.1)
    {
        $builder->where('id', $id);
    }

    public function scopeWhereParameterDefaultString(Builder $builder, string $id = '69')
    {
        $builder->where('id', $id);
    }

    public function scopeWhereParameterDefaultArray(Builder $builder, array $id = [])
    {
        $builder->where('id', $id);
    }

    public function scopeWhereParameterDefaultNull(Builder $builder, string $id = null)
    {
        $builder->where('id', $id);
    }
}
