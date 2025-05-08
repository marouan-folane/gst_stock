<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The alert instance.
     *
     * @var \App\Models\Alert
     */
    protected $alert;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['mail'];
        
        // Add twilio channel if phone number exists
        if (!empty($notifiable->phone_number)) {
            $channels[] = 'twilio';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('[ALERT] ' . $this->alert->title)
            ->line($this->alert->message);

        if ($this->alert->product_id && $this->alert->product) {
            $message->action('View Product', url('/products/' . $this->alert->product_id));
        }

        return $message->line('Thank you for using our inventory system.');
    }

    /**
     * Get the Twilio representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toTwilio($notifiable)
    {
        $productInfo = '';
        if ($this->alert->product_id && $this->alert->product) {
            $productInfo = ' - Product: ' . $this->alert->product->name;
        }
        
        return [
            'content' => "[ALERT] " . $this->alert->title . ": " . $this->alert->message . $productInfo
        ];
    }
} 