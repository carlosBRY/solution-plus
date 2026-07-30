<?php

namespace App\Providers;

use App\Models\Parametre;
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
        //
        View::composer('*', function ($view) {
            $view->with('parametre', Parametre::first() ?? new Parametre(['nom_cave' => 'Cave Prestige d\'Or', 'devise' => 'FCFA']));
        });
    }
}
