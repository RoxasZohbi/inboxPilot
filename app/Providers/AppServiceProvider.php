<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
        
        // Share unlisted pending count with dashboard layout
        View::composer('layouts.dashboard', \App\View\Composers\DashboardComposer::class);
    }
}
