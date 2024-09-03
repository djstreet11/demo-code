<?php

namespace App\Providers;

use App\Http\ViewComposers\WishlistViewComposer;
use App\View\Composers\FooterComposer;
use App\View\Composers\MenuComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('front.pages.parts.header', MenuComposer::class);
    }
}
