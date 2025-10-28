<?php

namespace romanzipp\ModelDoc\Tests\Support;

class ModelCustomCastSpecific extends ModelParent
{
    protected $casts = [
        'column_string' => 'datetime:' . \DateTime::ATOM,
    ];
}
