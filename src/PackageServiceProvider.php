<?php


namespace hosseinkalateh\SimpleDto;

use hosseinkalateh\SimpleDto\Commands\MakeDtoCommand;
use Illuminate\Support\ServiceProvider;

final class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // You can publish your stubs if you want to make them customizable by users
        $this->publishes([
            __DIR__ . '/../stubs' => resource_path('stubs/vendor/dto'),
        ]);
    }

    public function register()
    {
        $this->commands([
            MakeDtoCommand::class,
        ]);
    }
}
