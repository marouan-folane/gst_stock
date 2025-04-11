<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestTwilioNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio:test {phone? : The phone number to send the test SMS to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Twilio SMS notification system';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $phone = $this->argument('phone') ?: env('DEFAULT_SMS_RECIPIENT', '+212703948136');
        
        if (empty($phone)) {
            $this->error('No phone number provided and no default recipient set in .env file.');
            return 1;
        }
        
        $this->info("Testing Twilio notification to: $phone");
        
        // Check Twilio configuration
        $twilioSid = config('services.twilio.sid');
        $twilioToken = config('services.twilio.token');
        $twilioFrom = config('services.twilio.from');
        
        if (empty($twilioSid) || empty($twilioToken) || empty($twilioFrom)) {
            $this->error('Twilio configuration is incomplete. Please check your .env file.');
            $this->line('Required: TWILIO_SID, TWILIO_AUTH_TOKEN, TWILIO_FROM');
            return 1;
        }
        
        // Create a test user with the provided phone number
        $user = new User();
        $user->phone_number = $phone;
        
        // Create a test alert
        $this->info('Creating test alert...');
        try {
            $alert = Alert::create([
                'title' => 'Twilio Test Alert',
                'message' => 'This is a test SMS via Twilio from your Stock Management system.',
                'type' => 'info',
                'is_read' => false,
            ]);
            $this->info('Test alert created with ID: ' . $alert->id);
        } catch (\Exception $e) {
            $this->error('Failed to create test alert: ' . $e->getMessage());
            return 1;
        }
        
        // Send the notification
        try {
            $this->info('Sending notification via Twilio...');
            Notification::send($user, new StockAlertNotification($alert));
            $this->info('Twilio notification sent successfully!');
            $this->info('Message should arrive on your phone shortly.');
            
            // Clean up
            $alert->delete();
            $this->info('Test alert deleted from database.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send Twilio notification:');
            $this->error($e->getMessage());
            
            // Clean up
            if ($alert->exists) {
                $alert->delete();
                $this->info('Test alert deleted from database.');
            }
            return 1;
        }
    }
} 