<?php

namespace romanzipp\ModelDoc\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use romanzipp\ModelDoc\Console\Commands\GenerateModelDocumentationCommand;
use romanzipp\ModelDoc\Services\DocumentationGenerator;

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
            'seo'
        );

        $this->commands([
            GenerateModelDocumentationCommand::class,
        ]);

        $this->app->singleton(DocumentationGenerator::class, function (Application $app) {
            return new DocumentationGenerator();
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
