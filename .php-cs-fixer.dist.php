<?php

return romanzipp\Fixer\Config::make()
    ->in(__DIR__)
    ->exclude('ModelWithExistingPhpDocAndAttributeMultiline.php')
    ->preset(
        new romanzipp\Fixer\Presets\PrettyLaravel()
    )
    ->withRules([
        'braces' => false,
    ])
    ->out();
