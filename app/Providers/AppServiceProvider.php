<?php

namespace App\Providers;

use App\Support\MarkCraftShell;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::share('markcraftDesktop', MarkCraftShell::isDesktop());
        View::share('markcraftAllowsRegister', MarkCraftShell::allowsRegister());
    }
}
