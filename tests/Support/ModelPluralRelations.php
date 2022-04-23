<?php

namespace romanzipp\ModelDoc\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use romanzipp\ModelDoc\Tests\Support\Related\RelatedModel;
use romanzipp\ModelDoc\Tests\Support\Related\RelatedPivotModel;

class ModelPluralRelations extends Model
{
    public function votesUp(): Relations\HasManyThrough
    {
        return $this->hasManyThrough(RelatedModel::class, RelatedPivotModel::class);
    }

    public function votesDown(): Relations\HasManyThrough
    {
        return $this->hasManyThrough(RelatedModel::class, RelatedPivotModel::class);
    }
}
