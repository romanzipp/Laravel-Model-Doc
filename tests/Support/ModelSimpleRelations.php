<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use romanzipp\ModelDoc\Tests\Support\Related\RelatedModel;
use romanzipp\ModelDoc\Tests\Support\Related\RelatedPivotModel;

class ModelSimpleRelations extends Model
{
    public function belongsToRelation(): Relations\BelongsTo
    {
        return $this->belongsTo(RelatedModel::class);
    }

    public function belongsToManyRelation(): Relations\BelongsToMany
    {
        return $this->belongsToMany(RelatedModel::class);
    }

    public function hasOneRelation(): Relations\HasOne
    {
        return $this->hasOne(RelatedModel::class);
    }

    public function hasOneThroughRelation(): Relations\HasOneThrough
    {
        return $this->hasOneThrough(RelatedModel::class, RelatedPivotModel::class);
    }

    public function hasManyRelation(): Relations\HasMany
    {
        return $this->hasMany(RelatedModel::class);
    }

    public function hasManyThroughRelation(): Relations\HasManyThrough
    {
        return $this->hasManyThrough(RelatedModel::class, RelatedPivotModel::class);
    }
}
