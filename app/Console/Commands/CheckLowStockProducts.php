<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckLowStockProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products that are below their minimum stock level';

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
        $this->info("Checking for products with low stock levels...");
        
        // Get all products with stock below minimum level
        $products = Product::where('current_stock', '<=', DB::raw('min_stock'))
            ->where('current_stock', '>', 0)
            ->where('is_active', true)
            ->get();
            
        $alertCount = 0;
        
        foreach ($products as $product) {
            // Check if an alert already exists for this product's low stock
            $existingAlert = Alert::where('product_id', $product->id)
                ->where('type', 'warning')
                ->whereDate('created_at', Carbon::today())
                ->first();
            
            if (!$existingAlert) {
                $alert = Alert::create([
                    'title' => 'Low Stock Alert',
                    'message' => "Product {$product->name} (SKU: {$product->code}) is low in stock. Current stock: {$product->current_stock}, Minimum required: {$product->min_stock}.",
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
                
                $this->line("Alert created for {$product->name} - Current stock: {$product->current_stock}, Min: {$product->min_stock}");
                $this->line("SMS notification sent to " . $users->count() . " users via Twilio");
                $alertCount++;
            }
        }
        
        // Also check for out of stock products
        $outOfStockProducts = Product::where('current_stock', '<=', 0)
            ->where('is_active', true)
            ->get();
            
        foreach ($outOfStockProducts as $product) {
            // Check if an alert already exists for this product being out of stock
            $existingAlert = Alert::where('product_id', $product->id)
                ->where('type', 'danger')
                ->whereDate('created_at', Carbon::today())
                ->first();
            
            if (!$existingAlert) {
                $alert = Alert::create([
                    'title' => 'Out of Stock Alert',
                    'message' => "Product {$product->name} (SKU: {$product->code}) is out of stock. Please restock as soon as possible.",
                    'type' => 'danger',
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
                
                $this->line("Alert created for {$product->name} - Out of stock");
                $this->line("SMS notification sent to " . $users->count() . " users");
                $alertCount++;
            }
        }
        
        $this->info("Completed! $alertCount stock alerts have been generated.");
        
        return 0;
    }
}
