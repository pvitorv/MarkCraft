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
            $cms = Cms::all();
            $view->with('cms', $cms);
            $view->with('cmsBlog', $cms['blog'] ?? config('markcraft.blog'));
            $view->with('cmsFooter', $cms['footer'] ?? []);
            $view->with('cmsAds', $cms['ads'] ?? []);
            $view->with('cmsAnalytics', Cms::analytics());
            $view->with('cmsHome', $cms['home'] ?? []);
            $view->with('cmsStudio', $cms['studio'] ?? []);
            $view->with('cmsAffiliatePacks', $cms['affiliate_packs'] ?? config('markcraft.affiliate_packs'));
            $view->with('cmsPacksHub', array_merge(config('markcraft.packs_hub', []), $cms['packs_hub'] ?? []));
            $view->with('cmsDonations', array_merge(config('markcraft.donations', []), $cms['donations'] ?? []));
            $view->with('cmsTestimonials', $cms['testimonials'] ?? []);
            $view->with('cmsLanding', Cms::landing());
            $view->with('cmsNewsletter', array_merge(Cms::defaults()['newsletter'] ?? [], $cms['newsletter'] ?? []));
            $view->with('cmsHostingPartner', array_merge(Cms::defaults()['hosting_partner'] ?? [], $cms['hosting_partner'] ?? []));
        });
    }
}
