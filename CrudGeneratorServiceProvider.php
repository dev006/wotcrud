<?php

namespace Wot\CrudGenerator;

use Illuminate\Support\ServiceProvider;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(
            'Wot\CrudGenerator\Commands\CrudCommand',
            'Wot\CrudGenerator\Commands\CrudControllerCommand',
            'Wot\CrudGenerator\Commands\CrudRepositoryCommand',
            'Wot\CrudGenerator\Commands\CrudModelCommand',
            'Wot\CrudGenerator\Commands\CrudMigrationCommand',
            'Wot\CrudGenerator\Commands\CrudViewCommand',
            'Wot\CrudGenerator\Commands\CrudRemoveCommand'
        );
    }

    public function boot()
    {

         if(!file_exists($path = storage_path('wot_crud')))
            mkdir($path, 0777, true);

        $this->publishes([
            __DIR__ . '/config/posts.json' => config_path('posts.json'),
        ]);

        $this->publishes([
            __DIR__ . '/stubs/' => base_path('resources/wot_crud/'),
        ]);
    }
}
