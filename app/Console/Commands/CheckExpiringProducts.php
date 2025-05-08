<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckExpiringProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:check-expiring {days=30 : Number of days to check for expiry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products that will expire within the specified number of days';

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
        $days = $this->argument('days');
        $this->info("Checking for products expiring within $days days...");

        // Get all products with expiry dates within the specified range
        $products = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', Carbon::now())
            ->where('expiry_date', '<=', Carbon::now()->addDays($days))
            ->where('current_stock', '>', 0)
            ->get();

        $alertCount = 0;

        foreach ($products as $product) {
            // Check if an alert already exists for this product's expiry
            $existingAlert = Alert::where('product_id', $product->id)
                ->where('type', 'warning')
                ->whereDate('created_at', Carbon::today())
                ->first();

            if (!$existingAlert) {
                $daysRemaining = Carbon::now()->diffInDays(Carbon::parse($product->expiry_date));

                $alert = Alert::create([
                    'title' => 'Product Expiring Soon',
                    'message' => "Product {$product->name} (SKU: {$product->code}) will expire in {$daysRemaining} days.",
                    'type' => 'warning',
                    'product_id' => $product->id,
                    'is_read' => false,
                ]);

                // Send SMS notifications to admin and manager users
                $users = User::whereIn('role', ['admin', 'manager'])
                    ->whereNotNull('phone_number')
                    ->get();

                // If no users have phone numbers, create a default user with the default phone number
                if ($users->isEmpty() && env('DEFAULT_SMS_RECIPIENT')) {
                    $defaultUser = new User();
                    $defaultUser->phone_number = env('DEFAULT_SMS_RECIPIENT');
                    $users = collect([$defaultUser]);
                    $this->line("No users with phone numbers found. Using default recipient: " . env('DEFAULT_SMS_RECIPIENT'));
                }

                Notification::send($users, new StockAlertNotification($alert));

                $this->line("Alert created for {$product->name} - Expires in {$daysRemaining} days");
                $this->line("SMS notification sent to " . $users->count() . " users via Twilio");
                $alertCount++;
            }
        }

        $this->info("Completed! $alertCount expiry alerts have been generated.");

        return 0;
    }
}
