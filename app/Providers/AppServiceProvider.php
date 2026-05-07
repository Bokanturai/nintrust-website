<?php

namespace App\Providers;


use Illuminate\Support\Facades\Cache;
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
        View::composer('*', function ($view) {
            $settings = (object) [
                'site_name' => 'NIN TRUST',
                'short_name' => 'NIN TRUST',
                'logo' => 'img/logo.png',
                'mini_logo' => 'img/logo.png',
                'favicon' => 'img/logo.png',
                'login_background_image' => 'img/verification-hero.png',
                'registration_background_image' => 'img/verification-hero.png',
                'home_enabled' => true,
                'login_enabled' => true,
                'register_enabled' => true,
            ];

            $view->with('settings', $settings);
        });
    }
}
