<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register Twilio client as a singleton
        $this->app->singleton(\Twilio\Rest\Client::class, function ($app) {
            $config = $app['config']['services.twilio'];
            return new \Twilio\Rest\Client(
                $config['sid'], 
                $config['token']
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        
        // Register Twilio notification channel
        \Illuminate\Support\Facades\Notification::extend('twilio', function ($app) {
            return new \App\Notifications\Channels\TwilioSmsChannel(
                $app->make(\Twilio\Rest\Client::class),
                config('services.twilio.from')
            );
        });
    }
}
