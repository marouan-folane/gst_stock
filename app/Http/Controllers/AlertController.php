<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AlertController extends Controller
{
    /**
     * Display a listing of the alerts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Alert::with('product');
        
        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by read status if provided
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
        }
        
        $alerts = $query->latest()->paginate(15);
        
        return view('alerts.index', compact('alerts'));
    }

    /**
     * Mark an alert as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsRead($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->is_read = true;
        $alert->save();
        
        return back()->with('success', 'Alert marked as read');
    }

    /**
     * Mark all alerts as read.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead()
    {
        Alert::where('is_read', false)->update(['is_read' => true]);
        
        return back()->with('success', 'All alerts marked as read');
    }

    /**
     * Send notifications for an alert.
     *
     * @param  Alert  $alert
     * @return void
     */
    private function sendAlertNotifications(Alert $alert)
    {
        // Get notification settings
        $settings = json_decode(DB::table('settings')->where('key', 'notification_settings')->value('value') ?? '{}', true) ?: [];
        
        // Check if we need to send notifications for this type of alert
        $shouldNotify = true;
        if ($alert->type === 'warning' && strpos($alert->title, 'Low Stock') !== false) {
            $shouldNotify = $settings['notify_low_stock'] ?? true;
        } elseif ($alert->type === 'danger' && strpos($alert->title, 'Out of Stock') !== false) {
            $shouldNotify = $settings['notify_out_of_stock'] ?? true;
        } elseif (strpos($alert->title, 'Expiring') !== false) {
            $shouldNotify = $settings['notify_expiring'] ?? true;
        }
        
        if (!$shouldNotify) {
            \Log::info('Alert notification skipped based on settings', [
                'alert_id' => $alert->id,
                'alert_type' => $alert->type,
                'alert_title' => $alert->title
            ]);
            return;
        }
        
        // Determine which roles should receive notifications
        $roles = [];
        if ($settings['notify_admin'] ?? true) $roles[] = 'admin';
        if ($settings['notify_manager'] ?? true) $roles[] = 'manager';
        if ($settings['notify_employee'] ?? false) $roles[] = 'employee';
        
        // Get users with the selected roles who have email addresses
        $users = User::whereIn('role', $roles)
            ->whereNotNull('email')
            ->get();
            
        // Add any additional email recipients
        $additionalEmails = $settings['additional_emails'] ?? '';
        if (!empty($additionalEmails)) {
            $emails = array_map('trim', explode(',', $additionalEmails));
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $userObj = new \stdClass();
                    $userObj->email = $email;
                    $userObj->name = 'Additional Recipient';
                    $users->push($userObj);
                }
            }
        }
            
        // Send notifications to these users via email
        Notification::send($users, new StockAlertNotification($alert));
        
        // Log notification
        \Log::info('Alert notification sent', [
            'alert_id' => $alert->id,
            'recipients' => $users->count(),
            'emails' => $users->pluck('email')->toArray()
        ]);
    }

    /**
     * Generate alerts for products approaching expiry.
     *
     * @param  int  $days Days threshold for expiry warning
     * @return \Illuminate\Http\Response
     */
    public function checkExpiringProducts($days = 30)
    {
        // Get all products with expiry dates
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
                
                // Send notifications for the new alert
                $this->sendAlertNotifications($alert);
                
                $alertCount++;
            }
        }
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'alerts_created' => $alertCount]);
        }
        
        return back()->with('success', "{$alertCount} expiry alerts have been generated.");
    }

    /**
     * Generate alerts for products that are low in stock.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkLowStockProducts()
    {
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
                
                // Send notifications for the new alert
                $this->sendAlertNotifications($alert);
                
                $alertCount++;
            }
        }
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'alerts_created' => $alertCount]);
        }
        
        return back()->with('success', "{$alertCount} low stock alerts have been generated.");
    }

    /**
     * Generate alerts for products that are out of stock.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkOutOfStockProducts()
    {
        // Get all products that are out of stock
        $products = Product::where('current_stock', '<=', 0)
            ->where('is_active', true)
            ->get();
            
        $alertCount = 0;
        
        foreach ($products as $product) {
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
                
                // Send notifications for the new alert
                $this->sendAlertNotifications($alert);
                
                $alertCount++;
            }
        }
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'alerts_created' => $alertCount]);
        }
        
        return back()->with('success', "{$alertCount} out of stock alerts have been generated.");
    }

    /**
     * Delete an alert.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->delete();
        
        return back()->with('success', 'Alert has been deleted');
    }
    
    /**
     * Delete multiple alerts.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteMultiple(Request $request)
    {
        $validated = $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'exists:alerts,id',
        ]);
        
        Alert::whereIn('id', $request->alert_ids)->delete();
        
        return back()->with('success', count($request->alert_ids) . ' alerts have been deleted');
    }
    
    /**
     * Delete all alerts.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request)
    {
        $query = Alert::query();
        
        // Apply filters if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
        }
        
        $count = $query->count();
        $query->delete();
        
        return back()->with('success', $count . ' alerts have been deleted');
    }
}
