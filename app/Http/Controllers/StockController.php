<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Activity;
use App\Models\Supplier;
use App\Models\Alert;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;

class StockController extends Controller
{
    /**
     * Display a listing of stock adjustments
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('stock.index');
    }
    
    /**
     * Show the form for adding stock to a product
     *
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function add($productId)
    {
        $product = Product::findOrFail($productId);
        return view('stock.add', compact('product'));
    }
    
    /**
     * Process the stock addition
     *
     * @param \Illuminate\Http\Request $request
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $product = Product::findOrFail($productId);
        
        // Save old stock for comparison
        $oldStock = $product->current_stock;
        
        DB::beginTransaction();
        
        try {
            // Create stock adjustment record
            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'addition',
                'quantity' => $request->quantity,
                'previous_stock' => $product->current_stock,
                'new_stock' => $product->current_stock + $request->quantity,
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);
            
            // Update product stock
            $product->current_stock += $request->quantity;
            $product->save();
            
            // Record activity
            Activity::create([
                'user_id' => Auth::id(),
                'type' => 'stock',
                'description' => "Added {$request->quantity} stock to product '{$product->name}'",
                'link' => route('products.show', $product->id),
                'properties' => [
                    'product_id' => $product->id,
                    'quantity' => $request->quantity
                ]
            ]);
            
            // Check if alerts need to be created
            $this->checkProductForAlerts($product, $oldStock);
            
            DB::commit();
            
            return redirect()->route('products.show', $product->id)
                ->with('success', "Successfully added {$request->quantity} units to stock.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Failed to add stock: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the form for removing stock from a product
     *
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function remove($productId)
    {
        $product = Product::findOrFail($productId);
        return view('stock.remove', compact('product'));
    }
    
    /**
     * Process the stock removal
     *
     * @param \Illuminate\Http\Request $request
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $product = Product::findOrFail($productId);
        
        // Check if there is enough stock
        if ($product->current_stock < $request->quantity) {
            return back()->with('error', 'Not enough stock available. Current stock: ' . $product->current_stock);
        }
        
        // Save old stock for comparison
        $oldStock = $product->current_stock;
        
        DB::beginTransaction();
        
        try {
            // Create stock adjustment record
            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'removal',
                'quantity' => $request->quantity,
                'previous_stock' => $product->current_stock,
                'new_stock' => $product->current_stock - $request->quantity,
                'reference' => $request->reason,
                'notes' => $request->notes,
            ]);
            
            // Update product stock
            $product->current_stock -= $request->quantity;
            $product->save();
            
            // Record activity
            Activity::create([
                'user_id' => Auth::id(),
                'type' => 'stock',
                'description' => "Removed {$request->quantity} stock from product '{$product->name}'. Reason: {$request->reason}",
                'link' => route('products.show', $product->id),
                'properties' => [
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'reason' => $request->reason
                ]
            ]);
            
            // Check if alerts need to be created
            $this->checkProductForAlerts($product, $oldStock);
            
            DB::commit();
            
            return redirect()->route('products.show', $product->id)
                ->with('success', "Successfully removed {$request->quantity} units from stock.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Failed to remove stock: ' . $e->getMessage());
        }
    }
    
    /**
     * Show stock adjustment history for a specific product
     *
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function history($productId)
    {
        $product = Product::findOrFail($productId);
        
        $adjustments = StockAdjustment::with('user')
            ->where('product_id', $productId)
            ->latest()
            ->paginate(20);
            
        return view('stock.history', compact('product', 'adjustments'));
    }

    /**
     * Show the form for adjusting stock (add or remove)
     *
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function createStock($productId)
    {
        $product = Product::findOrFail($productId);
        $suppliers = Supplier::orderBy('name')->get();
        return view('products.stock.create', compact('product', 'suppliers'));
    }

    /**
     * Process the stock adjustment (add or remove)
     *
     * @param \Illuminate\Http\Request $request
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function storeStock(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'type' => 'required|in:add,remove',
            'reference_type' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);
        
        $product = Product::findOrFail($productId);
        
        // Check if there is enough stock for removal
        if ($request->type == 'remove' && $product->current_stock < $request->quantity) {
            return back()->with('error', 'Not enough stock available. Current stock: ' . $product->current_stock);
        }
        
        // Save old stock for comparison
        $oldStock = $product->current_stock;
        
        DB::beginTransaction();
        
        try {
            $isAddition = $request->type == 'add';
            $adjustmentType = $isAddition ? 'addition' : 'removal';
            $newStock = $isAddition 
                ? $product->current_stock + $request->quantity 
                : $product->current_stock - $request->quantity;
            
            // Create stock adjustment record
            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $adjustmentType,
                'quantity' => $request->quantity,
                'previous_stock' => $product->current_stock,
                'new_stock' => $newStock,
                'reference' => $request->reference_type . ($request->reference ? ' - ' . $request->reference : ''),
                'notes' => $request->notes,
                'date' => $request->date,
                'supplier_id' => $request->supplier_id,
            ]);
            
            // Update product stock
            $product->current_stock = $newStock;
            $product->save();
            
            // Record activity
            $action = $isAddition ? 'Added' : 'Removed';
            Activity::create([
                'user_id' => Auth::id(),
                'type' => 'stock',
                'description' => "{$action} {$request->quantity} stock to product '{$product->name}'",
                'link' => route('products.show', $product->id),
                'properties' => [
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'reference_type' => $request->reference_type,
                    'reference' => $request->reference
                ]
            ]);
            
            // Check if alerts need to be created
            $this->checkProductForAlerts($product, $oldStock);
            
            DB::commit();
            
            return redirect()->route('products.show', $product->id)
                ->with('success', "Successfully {$action} {$request->quantity} units " . ($isAddition ? "to" : "from") . " stock.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if product needs alerts and send notifications
     * 
     * @param Product $product
     * @param int $oldStock
     * @return int Number of alerts created
     */
    private function checkProductForAlerts(Product $product, $oldStock)
    {
        $alertsCreated = 0;
        
        // Check for low stock
        if ($product->current_stock <= $product->min_stock && $product->current_stock > 0) {
            // Only create alert if stock was previously above minimum
            if ($oldStock > $product->min_stock) {
                $this->createProductAlert(
                    $product,
                    'Low Stock Alert',
                    "Product {$product->name} (SKU: {$product->code}) is low in stock. Current stock: {$product->current_stock}, Minimum required: {$product->min_stock}.",
                    'warning'
                );
                $alertsCreated++;
                
                // Send email for any product under minimum stock threshold
                try {
                    // Create a collection with just this product
                    $products = collect([$product]);
                    
                    // Add more logging to see what's happening
                    \Illuminate\Support\Facades\Log::info("Attempting to send low stock email for " . $product->name, [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'current_stock' => $product->current_stock,
                        'min_stock' => $product->min_stock,
                        'email' => 'marouanfolane@gmail.com'
                    ]);
                    
                    // Send immediately without queueing
                    \Illuminate\Support\Facades\Mail::mailer('smtp')
                        ->to('marouanfolane@gmail.com')
                        ->send(new \App\Mail\LowStockMail($products));
                        
                    // Log the sent notification
                    \Illuminate\Support\Facades\Log::info("Low stock email notification sent from StockController", [
                        'product_name' => $product->name,
                        'current_stock' => $product->current_stock,
                        'min_stock' => $product->min_stock,
                        'email' => 'marouanfolane@gmail.com'
                    ]);
                } catch (\Exception $e) {
                    // Detailed error logging
                    \Illuminate\Support\Facades\Log::error("Failed to send low stock email notification from StockController", [
                        'product_id' => $product->id,
                        'error' => $e->getMessage(),
                        'error_trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }
        
        // Check for out of stock
        if ($product->current_stock <= 0 && $product->is_active) {
            // Only create alert if stock was previously above 0
            if ($oldStock > 0) {
                $this->createProductAlert(
                    $product,
                    'Out of Stock Alert',
                    "Product {$product->name} (SKU: {$product->code}) is out of stock. Please restock as soon as possible.",
                    'danger'
                );
                $alertsCreated++;
            }
        }
        
        // Check for sensible categories and send immediate email notifications
        if ($product->category_id) {
            $sensibleCategory = \App\Models\SensibleCategory::where('category_id', $product->category_id)
                ->where('is_active', true)
                ->first();
                
            if ($sensibleCategory && $product->current_stock <= $sensibleCategory->min_quantity) {
                // Only send email if stock previously wasn't below the threshold
                if ($oldStock > $sensibleCategory->min_quantity) {
                    try {
                        // Send immediate email notification
                        Mail::to($sensibleCategory->notification_email)
                            ->send(new \App\Mail\SensibleProductMail($product, $sensibleCategory->category));
                            
                        // Update last notification sent timestamp
                        $sensibleCategory->last_notification_sent = now();
                        $sensibleCategory->save();
                        
                        // Log the sent notification
                        \Illuminate\Support\Facades\Log::info("Immediate sensible category notification sent", [
                            'category_id' => $sensibleCategory->category_id,
                            'category_name' => $sensibleCategory->category->name,
                            'product_name' => $product->name,
                            'current_stock' => $product->current_stock,
                            'min_quantity' => $sensibleCategory->min_quantity,
                            'email' => $sensibleCategory->notification_email
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send immediate sensible category notification", [
                            'category_id' => $sensibleCategory->category_id,
                            'product_id' => $product->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
        
        return $alertsCreated;
    }
    
    /**
     * Create product alert and send notifications
     * 
     * @param Product $product
     * @param string $title
     * @param string $message
     * @param string $type
     * @return Alert
     */
    private function createProductAlert(Product $product, $title, $message, $type = 'warning')
    {
        // Check if an alert already exists for this product with the same title (today)
        $existingAlert = Alert::where('product_id', $product->id)
            ->where('title', $title)
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->first();
        
        // If alert already exists, don't create a duplicate
        if ($existingAlert) {
            return $existingAlert;
        }
        
        // Create new alert
        $alert = Alert::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'product_id' => $product->id,
            'is_read' => false,
        ]);
        
        // Get users to notify
        $users = User::whereIn('role', ['admin', 'manager'])
            ->whereNotNull('phone_number')
            ->get();
            
        // If no users have phone numbers, create a default user with the default phone number
        if ($users->isEmpty() && env('DEFAULT_SMS_RECIPIENT')) {
            $defaultUser = new User();
            $defaultUser->phone_number = env('DEFAULT_SMS_RECIPIENT');
            $users = collect([$defaultUser]);
        }
        
        try {
            // Send notifications
            Notification::send($users, new StockAlertNotification($alert));
            
            // Log notification
            \Log::info('Stock adjustment alert notification sent', [
                'alert_id' => $alert->id,
                'product_id' => $product->id,
                'recipients' => $users->count(),
                'phone_numbers' => $users->pluck('phone_number')->toArray()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send stock adjustment alert notification', [
                'alert_id' => $alert->id,
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $alert;
    }
}
