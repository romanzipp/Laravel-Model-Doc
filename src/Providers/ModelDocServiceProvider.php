<?php

namespace romanzipp\ModelDoc\Providers;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use romanzipp\ModelDoc\Console\Commands\GenerateModelDocumentationCommand;
use romanzipp\ModelDoc\Services\DocumentationGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModelDocServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/../config/model-doc.php' => config_path('model-doc.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/../config/model-doc.php',
            'model-doc'
        );

        $this->commands([
            GenerateModelDocumentationCommand::class,
        ]);

        $this->app->singleton(DocumentationGenerator::class, function (Application $app) {
            return new DocumentationGenerator(
                output: $app->make(
                    OutputStyle::class, [
                        'input' => $app->make(InputInterface::class),
                        'output' => $app->make(OutputInterface::class),
                    ]
                )
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [DocumentationGenerator::class];
    }
}
