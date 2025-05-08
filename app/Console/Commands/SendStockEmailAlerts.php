<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Alert;
use App\Models\User;
use App\Mail\LowStockMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendStockEmailAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:send-emails {--low : Send low stock alerts} {--max : Send max stock alerts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send stock level email alerts to configured recipients';

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
        // Get notification settings
        $settings = json_decode(DB::table('settings')->where('key', 'notification_settings')->value('value') ?? '{}', true) ?: [];
        
        // Determine which types of alerts to send
        $sendLow = $this->option('low') || (!$this->option('low') && !$this->option('max')); 
        $sendMax = $this->option('max') || (!$this->option('low') && !$this->option('max'));
        
        // Count for reporting
        $emailCount = 0;
        
        // Determine which roles should receive notifications
        $roles = [];
        if ($settings['notify_admin'] ?? true) $roles[] = 'admin';
        if ($settings['notify_manager'] ?? true) $roles[] = 'manager';
        if ($settings['notify_employee'] ?? false) $roles[] = 'employee';
        
        // Get users with appropriate roles
        $recipients = User::whereIn('role', $roles)
            ->whereNotNull('email')
            ->pluck('email')
            ->toArray();
            
        // Add any additional email recipients
        $additionalEmails = $settings['additional_emails'] ?? '';
        if (!empty($additionalEmails)) {
            $emails = array_map('trim', explode(',', $additionalEmails));
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = $email;
                }
            }
        }
        
        // Also send to marouanfolane@gmail.com if specified in the settings
        $defaultEmail = env('DEFAULT_ALERT_EMAIL');
        if ($defaultEmail && !in_array($defaultEmail, $recipients)) {
            $recipients[] = $defaultEmail;
        }
        
        if (count($recipients) == 0) {
            $this->error('No recipients configured for email alerts.');
            return 0;
        }
        
        // Send low stock notifications
        if ($sendLow && ($settings['notify_low_stock'] ?? true)) {
            $lowStockProducts = Product::where(function($query) {
                    $query->where('current_stock', '<=', DB::raw('min_stock')) // Low stock
                          ->orWhere('current_stock', '<=', 0); // Out of stock
                })
                ->where('is_active', true)
                ->get();
                
            if ($lowStockProducts->count() > 0) {
                $this->info("Found {$lowStockProducts->count()} products with low stock.");
                
                foreach ($recipients as $email) {
                    try {
                        $this->line("  - Sending low stock notification to {$email}");
                        
                        Mail::mailer('smtp')
                            ->to($email)
                            ->send(new LowStockMail($lowStockProducts, 'low'));
                            
                        $emailCount++;
                        
                        // Create an alert entry for this notification
                        Alert::create([
                            'title' => 'Low Stock Email Sent',
                            'message' => "Automatic low stock email notification sent to {$email} for {$lowStockProducts->count()} products.",
                            'type' => 'info',
                            'is_read' => false,
                        ]);
                    } catch (\Exception $e) {
                        $this->error("  - Failed to send email to {$email}: {$e->getMessage()}");
                        Log::error("Failed to send low stock alert email", [
                            'email' => $email,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            } else {
                $this->info("No products with low stock found.");
            }
        }
        
        // Send max stock notifications
        if ($sendMax && ($settings['notify_max_stock'] ?? true)) {
            $maxStockProducts = Product::where('current_stock', '>', DB::raw('max_stock'))
                ->where('is_active', true)
                ->where('max_stock', '>', 0) // Only consider products with max_stock set
                ->get();
                
            if ($maxStockProducts->count() > 0) {
                $this->info("Found {$maxStockProducts->count()} products exceeding maximum stock.");
                
                foreach ($recipients as $email) {
                    try {
                        $this->line("  - Sending max stock notification to {$email}");
                        
                        Mail::mailer('smtp')
                            ->to($email)
                            ->send(new LowStockMail($maxStockProducts, 'max'));
                            
                        $emailCount++;
                        
                        // Create an alert entry for this notification
                        Alert::create([
                            'title' => 'Maximum Stock Email Sent',
                            'message' => "Automatic maximum stock email notification sent to {$email} for {$maxStockProducts->count()} products.",
                            'type' => 'info',
                            'is_read' => false,
                        ]);
                    } catch (\Exception $e) {
                        $this->error("  - Failed to send email to {$email}: {$e->getMessage()}");
                        Log::error("Failed to send max stock alert email", [
                            'email' => $email,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            } else {
                $this->info("No products exceeding maximum stock found.");
            }
        }
        
        $this->info("Sent a total of {$emailCount} email notifications.");
        return 0;
    }
} 