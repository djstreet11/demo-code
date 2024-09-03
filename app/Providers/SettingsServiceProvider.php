<?php

namespace App\Providers;

use App\Models\Settings;
use App\Repositories\SettingsRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('settings', function (Application $app) {
            $settings = (new SettingsRepository(new Settings()))->all();
            $arr = [];
            for ($i = 0; $i < count($settings); $i++) {
                $arr[$settings[$i]->key] = $settings[$i]->value;
            }

            return $arr;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
