<?php

namespace App\Providers;

use App\Http\View\Composers\DashboardComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Bind DashboardComposer to the dashboard view
        View::composer('dashboard', DashboardComposer::class);
    }
}
