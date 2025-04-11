<?php

namespace App\Providers;

use App\Notifications\Channels\TwilioSmsChannel;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;

class TwilioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('twilio', function ($app) {
            $config = $app['config']['services.twilio'];
            
            return new Client($config['sid'], $config['token']);
        });
        
        $this->app->singleton(TwilioSmsChannel::class, function ($app) {
            $config = $app['config']['services.twilio'];
            
            return new TwilioSmsChannel(
                $app->make('twilio'),
                $config['from']
            );
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