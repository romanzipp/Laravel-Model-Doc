<?php

return [
    'relations' => [
        // Enable generating PHPDoc properties for relation methods
        // @property MyRelatedModel|null $my_relation
        'enabled' => true,

        'counts' => [
            // Enable generating PHPDoc properties for relations count attributes
            // @property int|null $my_relation_counts
            'enabled' => true,
        ],

        'base_model' => \Illuminate\Database\Eloquent\Model::class,
    ],

    'attributes' => [
        // Enable generating PHPDoc properties for database columns
        'enabled' => true,
    ],

    'scopes' => [
        // Enable generating model query scope methods
        'enabled' => true,
        // Define certain scope methods that should be ignored (provide final method name without "scope" prefix)
        'ignore' => [
            // 'whereUuid',
        ],
    ],

    'fail_when_empty' => false,
];
