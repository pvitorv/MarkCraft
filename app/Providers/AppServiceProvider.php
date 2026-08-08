<?php

namespace App\Providers;

use App\Support\Cms;
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

        View::composer('*', function ($view) {
            static $cms = null;
            $cms ??= Cms::all();
            $view->with('cms', $cms);
            $view->with('cmsBlog', $cms['blog'] ?? config('markcraft.blog'));
            $view->with('cmsFooter', $cms['footer'] ?? []);
            $view->with('cmsAds', $cms['ads'] ?? []);
            $view->with('cmsHome', $cms['home'] ?? []);
            $view->with('cmsStudio', $cms['studio'] ?? []);
            $view->with('cmsAffiliatePacks', $cms['affiliate_packs'] ?? config('markcraft.affiliate_packs'));
            $view->with('cmsDonations', $cms['donations'] ?? config('markcraft.donations'));
        });
    }
}
