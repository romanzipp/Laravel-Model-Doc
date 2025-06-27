<?php

namespace romanzipp\ModelDoc\Console\Commands;

use Illuminate\Console\Command;
use romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException;
use romanzipp\ModelDoc\Services\DocumentationGenerator;

class GenerateModelDocumentationCommand extends Command
{
    protected $signature = 'model-doc:generate {--v}';

    public function handle(): void
    {
        $generator = new DocumentationGenerator(
            output: $this->output
        );

        $models = $generator->collectModels();

        foreach ($models as $model) {
            try {
                $generator->generate($model);

                $this->info("Wrote {$model->getName()}");
            } catch (ModelDocumentationFailedException $exception) {
                $this->warn("Failed {$model->getName()}: {$exception->getMessage()}");

                if ($this->option('v')) {
                    $this->warn($exception);
                }
            }
        }
    }
}
