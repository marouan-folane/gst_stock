<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\SensibleCategory;
use App\Notifications\SensibleProductAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SensibleProductMail;
use App\Mail\LowStockMail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckSensibleProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:sensible-products {--force : Force check regardless of notification frequency}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products under minimum stock in sensible categories and send email notifications';

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
        $this->info('Starting check for sensible products and products below minimum stock...');
        
        // Get notification settings
        $settings = json_decode(DB::table('settings')->where('key', 'notification_settings')->value('value') ?? '{}', true) ?: [];
        
        // Step 1: Check individual sensible products
        $this->checkSensibleProducts();
        
        // Step 2: Check products in sensible categories
        $this->checkSensibleCategories();
        
        // Step 3: Check all products below threshold (if enabled in settings)
        if ($settings['notify_low_stock'] ?? true) {
            $this->checkLowStockProducts();
        }
        
        $this->info('Finished checking products. Notifications have been sent if needed.');
        
        return 0;
    }
    
    /**
     * Check individual products marked as sensible
     */
    private function checkSensibleProducts()
    {
        $this->info('Skipping individual sensible products check (feature removed)...');
        // This functionality has been removed since the is_sensible and notification_email fields
        // have been removed from the products table
        return 0;
    }
    
    /**
     * Check products in sensible categories
     */
    private function checkSensibleCategories()
    {
        $this->info('Checking products in sensible categories...');
        
        // Get all active sensible categories
        $sensibleCategories = SensibleCategory::where('is_active', true)->get();
        $count = 0;
        $force = $this->option('force');
        
        foreach ($sensibleCategories as $sensibleCategory) {
            // For hourly checks, always send notifications regardless of frequency
            // but still respect the last_notification_sent time to avoid spamming
            $shouldSend = $force || $this->shouldSendNotification($sensibleCategory, true);
            
            if (!$shouldSend) {
                $this->line("  - Skipping {$sensibleCategory->category->name}: Already sent notification today");
                continue;
            }
            
            // Get category products that are low stock or out of stock
            $products = Product::where('category_id', $sensibleCategory->category_id)
                ->where(function($query) use ($sensibleCategory) {
                    $query->where('current_stock', '<=', $sensibleCategory->min_quantity) // Low stock
                          ->orWhere('current_stock', '<=', 0); // Out of stock
                })
                ->where('is_active', true)
                ->get();
                
            if ($products->count() > 0) {
                try {
                    // Send email with all products in this category that are below min_quantity
                    Mail::to($sensibleCategory->notification_email)
                        ->send(new SensibleProductMail($products, $sensibleCategory->category));
                    
                    $this->line("  - Sent notification for {$products->count()} products in category {$sensibleCategory->category->name} to {$sensibleCategory->notification_email}");
                    $count++;
                    
                    // Update last_notification_sent timestamp for this category
                    $sensibleCategory->last_notification_sent = now();
                    $sensibleCategory->save();
                    
                    // Log the sent notification
                    Log::info("Sensible category notification sent", [
                        'category_id' => $sensibleCategory->category_id,
                        'category_name' => $sensibleCategory->category->name,
                        'products_count' => $products->count(),
                        'email' => $sensibleCategory->notification_email,
                        'frequency' => $sensibleCategory->notification_frequency
                    ]);
                } catch (\Exception $e) {
                    $this->error("  - Failed to send notification for category {$sensibleCategory->category->name}: {$e->getMessage()}");
                    Log::error("Failed to send sensible category notification", [
                        'category_id' => $sensibleCategory->category_id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $this->line("  - No products below min_quantity ({$sensibleCategory->min_quantity}) in {$sensibleCategory->category->name}");
            }
        }
        
        $this->info("Sent {$count} notifications for sensible categories.");
    }
    
    /**
     * Check if notification should be sent today based on frequency
     *
     * @param SensibleCategory $category
     * @param bool $hourlyCheck Whether this is being called from hourly check
     * @return bool
     */
    private function shouldSendNotification(SensibleCategory $category, $hourlyCheck = false)
    {
        // If there's no last_notification_sent, we should send it
        if (empty($category->last_notification_sent)) {
            return true;
        }
        
        $lastSent = Carbon::parse($category->last_notification_sent);
        $now = Carbon::now();
        
        // For hourly checks, only allow notifications if the last one wasn't sent in the past hour
        if ($hourlyCheck) {
            return $lastSent->diffInHours($now) >= 1;
        }
        
        // For normal checks, use the notification frequency
        switch ($category->notification_frequency) {
            case 'daily':
                // Send if the last notification was not today
                return !$lastSent->isToday();
                
            case 'weekly':
                // Send if the last notification was 7 or more days ago
                return $lastSent->diffInDays($now) >= 7;
                
            case 'monthly':
                // Send if the last notification was in a different month
                return $lastSent->month != $now->month || $lastSent->year != $now->year;
                
            default:
                // Default to daily
                return !$lastSent->isToday();
        }
    }
    
    /**
     * Check all products with low stock
     */
    private function checkLowStockProducts()
    {
        $this->info('Checking all products with low stock...');
        
        // Get notification settings
        $settings = json_decode(DB::table('settings')->where('key', 'notification_settings')->value('value') ?? '{}', true) ?: [];
        
        // Determine which roles should receive notifications
        $roles = [];
        if ($settings['notify_admin'] ?? true) $roles[] = 'admin';
        if ($settings['notify_manager'] ?? true) $roles[] = 'manager';
        if ($settings['notify_employee'] ?? false) $roles[] = 'employee';
        
        // Get all products with stock below minimum level (except those already handled by sensible categories)
        $products = Product::where(function($query) {
                $query->where('current_stock', '<=', DB::raw('min_stock')) // Low stock
                      ->orWhere('current_stock', '<=', 0); // Out of stock
            })
            ->where('is_active', true)
            ->whereNotIn('category_id', function($query) {
                $query->select('category_id')
                      ->from('sensible_categories')
                      ->where('is_active', true);
            })
            ->get();
            
        if ($products->count() > 0) {
            // Send to users with appropriate roles
            $recipients = \App\Models\User::whereIn('role', $roles)
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
            
            // Also send to marouanfolane@gmail.com (user's requested email)
            if (!in_array('marouanfolane@gmail.com', $recipients)) {
                $recipients[] = 'marouanfolane@gmail.com';
            }
            
            if (count($recipients) > 0) {
                try {
                    foreach ($recipients as $email) {
                        try {
                            // Log attempt to send email
                            $this->line("  - Attempting to send email to {$email}");
                            
                            // Use mailer directly for more reliable sending
                            \Illuminate\Support\Facades\Mail::mailer('smtp')
                                ->to($email)
                                ->send(new LowStockMail($products));
                            
                            $this->line("  - Successfully sent email to {$email}");
                            
                            // Add a short pause to prevent rate limiting
                            usleep(500000); // 0.5 seconds
                        } catch (\Exception $e) {
                            $this->error("  - Failed to send email to {$email}: {$e->getMessage()}");
                            Log::error("Failed to send low stock notification to recipient", [
                                'email' => $email,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                    
                    $this->info("  - Sent notifications for {$products->count()} products to " . count($recipients) . " recipients");
                    
                    // Log the sent notification
                    Log::info("Low stock notification sent", [
                        'products_count' => $products->count(),
                        'recipients_count' => count($recipients)
                    ]);
                } catch (\Exception $e) {
                    $this->error("  - Failed to send low stock notifications: {$e->getMessage()}");
                    Log::error("Failed to send low stock notification", [
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $this->warn("  - No recipients configured for low stock notifications");
            }
        } else {
            $this->info("  - No products with low stock found");
        }
        
        $this->info("Completed check for low stock products.");
    }
}
