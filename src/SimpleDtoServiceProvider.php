<?php
namespace hosseinkalateh\SimpleDto;

use Illuminate\Support\ServiceProvider;
use hosseinkalateh\SimpleDto\Commands\MakeRequestCommand;

class SimpleDtoServiceProvider extends ServiceProvider {
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeRequestCommand::class,
            ]);
        }
    }
    public function register()
    {
        //
    }
}
?>
