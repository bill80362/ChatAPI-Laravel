<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\ResponseFactory;

class myServiceRpovider extends ServiceProvider
{
    protected $defer = true;
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
//        echo 123;
        $this->app->singleton('Hello', function(){ return 'Hi'; });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(ResponseFactory $response)
    {
//        echo 666;
        $response->macro('caps', function ($value) {
            //
            return $value;
        });
    }
}
