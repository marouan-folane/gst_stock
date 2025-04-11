<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class TwilioSmsChannel
{
    /**
     * The Twilio client instance.
     *
     * @var \Twilio\Rest\Client
     */
    protected $client;

    /**
     * The phone number notifications should be sent from.
     *
     * @var string
     */
    protected $from;

    /**
     * Create a new Twilio channel instance.
     *
     * @param  \Twilio\Rest\Client  $client
     * @param  string  $from
     * @return void
     */
    public function __construct(Client $client, $from)
    {
        $this->client = $client;
        $this->from = $from;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Twilio\Rest\Api\V2010\Account\MessageInstance|null
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('twilio', $notification)) {
            return null;
        }

        $message = $notification->toTwilio($notifiable);

        return $this->client->messages->create($to, [
            'from' => $this->from,
            'body' => $message,
        ]);
    }
} 