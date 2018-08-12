<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        //$this->app->bind(ClientInterface::class, Client::class);
//        $this->app->bind(UrlShortener::class, function () {
//            return new RequestableUrlShortener(
//                new UrlShortener(),
//                new Client()
//            );
//        });
    }
}
