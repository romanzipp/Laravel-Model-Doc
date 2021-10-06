<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use romanzipp\ModelDoc\Tests\Support\Related\RelatedModel;

class ModelMorphRelations extends Model
{
    public function morphToRelation(): Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function morphToRelationWithType(): Relations\MorphTo
    {
        return $this->morphTo('morphToRelationWithType', self::class);
    }

    public function morphOneRelation(): Relations\MorphOne
    {
        return $this->morphOne(RelatedModel::class, 'subject');
    }

    public function morphManyRelation(): Relations\MorphMany
    {
        return $this->morphMany(RelatedModel::class, 'subject');
    }

    public function morphToManyRelation(): Relations\MorphToMany
    {
        return $this->morphToMany(RelatedModel::class, 'subject');
    }

    public function morphedByManyRelation(): Relations\MorphToMany
    {
        return $this->morphedByMany(RelatedModel::class, 'subject');
    }
}
