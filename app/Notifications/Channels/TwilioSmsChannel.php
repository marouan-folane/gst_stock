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
            // Use phone_number as fallback
            $to = $notifiable->phone_number ?? null;
            
            if (!$to) {
                return null;
            }
        }

        $message = $notification->toTwilio($notifiable);
        $content = is_string($message) ? $message : $message['content'];

        try {
            $result = $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $content,
            ]);
            
            // Log successful SMS
            \Illuminate\Support\Facades\Log::info('SMS sent successfully via Twilio', [
                'to' => $to,
                'message_sid' => $result->sid,
                'status' => $result->status,
            ]);
            
            return $result;
        } catch (\Exception $e) {
            // Log error
            \Illuminate\Support\Facades\Log::error('Failed to send SMS via Twilio', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
} 