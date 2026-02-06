<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
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
        View::composer('layouts.navigation', function ($view) {
            if (Auth::check()) {
                $view->with('userUnreadNotifications', Auth::user()->unreadNotifications()->take(15)->get());
                $view->with('userUnreadNotificationsCount', Auth::user()->unreadNotifications()->count());
            } else {
                $view->with('userUnreadNotifications', collect());
                $view->with('userUnreadNotificationsCount', 0);
            }
        });
    }
}
