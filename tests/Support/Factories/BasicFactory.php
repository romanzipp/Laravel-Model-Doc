<?php

namespace romanzipp\ModelDoc\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use romanzipp\ModelDoc\Tests\Support\ModelFactoryBasic;

class BasicFactory extends Factory
{
    protected $model = ModelFactoryBasic::class;

    public function definition()
    {
        return [];
    }
}
