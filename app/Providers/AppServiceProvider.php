<?php

namespace App\Providers;

use App\Support\PublicCatalog;
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
        View::composer('components.public-layout', function ($view): void {
            $userId = auth()->id();
            $user = $userId ? PublicCatalog::findUser((int) $userId) : null;

            $view->with([
                'publicUser' => $user,
                'publicUnreadCount' => $user ? PublicCatalog::unreadNotificationCount((int) $user->id) : 0,
            ]);
        });
    }
}
