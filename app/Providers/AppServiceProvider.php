<?php

namespace App\Providers;

use App\Helpers\CreatorQuizHelper;
use App\Services\CreatorQuizService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CreatorQuizService::class, function ($app) {
            return new CreatorQuizService(
                $app->make(CreatorQuizHelper::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
