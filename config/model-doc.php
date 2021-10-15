<?php

return [
    // Generate PHPDoc properties for relation methods
    'relations' => [
        // @property MyRelatedModel|null $myRelation
        'enabled' => true,

        'counts' => [
            // Enable generating PHPDoc properties for relations count attributes
            // @property int|null $my_relation_counts
            'enabled' => true,
        ],

        // Base model class to be used for MorphTo relation return type
        'base_model' => \Illuminate\Database\Eloquent\Model::class,
    ],

    // Generate PHPDoc properties for database columns
    'attributes' => [
        'enabled' => true,
    ],

    // Generate properties for model accessors like `getTitleAttribute`
    'accessors' => [
        'enabled' => true,
    ],

    // Generate model query scope methods. Only looks for existing method prefixed with "scope"
    'scopes' => [
        // @method static \Illuminate\Database\Eloquent\Builder whereId(int $id)',
        'enabled' => true,
        // Define certain scope methods that should be ignored (provide final method name without "scope" prefix)
        'ignore' => [
            // 'whereUuid',
        ],
    ],

    'fail_when_empty' => false,
];
