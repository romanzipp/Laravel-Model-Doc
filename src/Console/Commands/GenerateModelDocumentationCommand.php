<?php

namespace romanzipp\ModelDoc\Console\Commands;

use Illuminate\Console\Command;
use ModelDocumentationFailedException;
use romanzipp\ModelDoc\Services\DocumentationGenerator;

class GenerateModelDocumentationCommand extends Command
{
    protected $signature = 'dev:ide';

    public function handle(DocumentationGenerator $generator): void
    {
        $models = $generator->collectModels();

        foreach ($models as $model) {
            try {
                $generator->generate($model);

                $this->info("Wrote {$model->getName()}");
            } catch (ModelDocumentationFailedException $exception) {
                $this->warn("Failed {$model->getName()}");
            }
        }
    }
}
