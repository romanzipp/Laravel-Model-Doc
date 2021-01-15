<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use romanzipp\ModelDoc\Tests\Support\Related\RelatedModel;

class MorphSimpleRelationsModel extends Model
{
    public function morphToRelation(): Relations\MorphTo
    {
        return $this->morphTo(RelatedModel::class);
    }

    public function morphOneRelation(): Relations\MorphOne
    {
        return $this->morphOne(RelatedModel::class);
    }

    public function morphManyRelation(): Relations\MorphMany
    {
        return $this->morphMany(RelatedModel::class);
    }

    public function morphToManyRelation(): Relations\MorphToMany
    {
        return $this->morphToMany(RelatedModel::class);
    }

    public function morphedByManyRelation(): Relations\MorphToMany
    {
        return $this->morphedByMany(RelatedModel::class);
    }
}
