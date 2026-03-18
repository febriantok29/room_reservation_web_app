<?php

namespace App\Providers;

use App\Models\RoomComplaint;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
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
        Paginator::useBootstrap();

        // Explicit route model binding so {complaint} resolves to RoomComplaint
        Route::model('complaint', RoomComplaint::class);
    }
}
