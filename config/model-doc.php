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
    ],

    'attributes' => [
        'enabled' => true,
    ],

    'fail_when_empty' => false,
];
