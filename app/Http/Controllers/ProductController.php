<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Alert;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);
        
        // Filter by category if provided
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
            $categoryName = Category::findOrFail($request->category_id)->name;
        }
        
        // Filter by supplier if provided
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
            $supplierName = Supplier::findOrFail($request->supplier_id)->name;
        }
        
        // Filter by stock status if provided
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->whereRaw('current_stock <= min_stock AND current_stock > 0');
                    break;
                case 'out_of_stock':
                    $query->where('current_stock', '<=', 0);
                    break;
                case 'in_stock':
                    $query->whereRaw('current_stock > min_stock');
                    break;
            }
        }
        
        // Filter by expiry status if provided
        if ($request->filled('expiry_status')) {
            switch ($request->expiry_status) {
                case 'expiring_30':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '>=', now())
                          ->where('expiry_date', '<=', now()->addDays(30));
                    break;
                case 'expiring_60':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '>=', now())
                          ->where('expiry_date', '<=', now()->addDays(60));
                    break;
                case 'expiring_90':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '>=', now())
                          ->where('expiry_date', '<=', now()->addDays(90));
                    break;
                case 'expired':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '<', now());
                    break;
            }
        }
        
        // Search by name or code if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Get sort field and direction
        $sortField = $request->input('sort', 'name');
        $sortDirection = $request->input('direction', 'asc');
        
        // Add sorting
        $query->orderBy($sortField, $sortDirection);
        
        $products = $query->paginate(10)->withQueryString();
        
        // Build filter data for the view
        $filters = [
            'filtered' => $request->hasAny(['category_id', 'supplier_id', 'stock_status', 'expiry_status', 'search']),
            'filterType' => null,
            'filterName' => null,
        ];
        
        if ($request->filled('category_id')) {
            $filters['filterType'] = 'category';
            $filters['filterName'] = $categoryName;
        } elseif ($request->filled('supplier_id')) {
            $filters['filterType'] = 'supplier';
            $filters['filterName'] = $supplierName;
        } elseif ($request->filled('stock_status')) {
            $filters['filterType'] = 'stock';
            $filters['filterName'] = ucfirst(str_replace('_', ' ', $request->stock_status));
        } elseif ($request->filled('expiry_status')) {
            $filters['filterType'] = 'expiry';
            $filters['filterName'] = ucfirst(str_replace('_', ' ', $request->expiry_status));
        }
        
        // Get categories and suppliers for the filter dropdowns
        $categories = Category::all();
        $suppliers = Supplier::all();
        
        return view('products.index', compact('products', 'categories', 'suppliers'))
            ->with($filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $categories = Category::all();
        $suppliers = Supplier::all();
        $selectedSupplierId = $request->input('supplier_id');
        $selectedCategoryId = $request->input('category_id');
        
        return view("products.create", compact("categories", "suppliers", "selectedSupplierId", "selectedCategoryId"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'expiry_date' => 'nullable|date',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);
    
        $product = new Product();
        $product->name = $request->name;
        $product->code = $request->code;
        $product->description = $request->description;
        $product->category_id = $request->category_id;
        $product->supplier_id = $request->supplier_id;
        $product->cost_price = $request->cost_price;
        $product->selling_price = $request->selling_price;
        $product->unit = $request->unit;
        $product->expiry_date = $request->expiry_date;
        $product->current_stock = $request->current_stock;
        $product->min_stock = $request->min_stock;
        $product->max_stock = $request->max_stock;
        $product->is_active = $request->has('is_active');
    
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product_images', 'public');
            $product->image = $imagePath;
        }
    
        $product->save();
        
        // Check if this product requires alerts and notify users
        $this->checkProductForAlerts($product);
    
        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stockMovements = StockMovement::where('product_id', $id)->latest()->take(10)->get();
        $product = Product::findOrFail($id);
        
        // Get recent sales for this product from sale_items table
        $sales = \App\Models\SaleItem::where('product_id', $id)
                    ->with('sale')
                    ->latest()
                    ->take(10)
                    ->get();
        
        return view("products.show", compact("product", "stockMovements", "sales"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    { 
        $suppliers=Supplier::all();
        $categories=Category::all();
        $product=Product::findOrFail($id);
        return view("products.edit",compact("product","categories","suppliers"));
    }
   

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code,'.$id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'expiry_date' => 'nullable|date',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);
    
        $product = Product::findOrFail($id);
        
        // Save old stock for comparison
        $oldStock = $product->current_stock;
        $oldExpiryDate = $product->expiry_date;
        
        $product->update($request->except('image'));
    
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product_images', 'public');
            $product->image = $imagePath;
        }
    
        $product->save();
        
        // Check if product requires alerts after update
        $this->checkProductForAlerts($product, $oldStock, $oldExpiryDate);
    
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
    
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
    
    /**
     * Display a listing of products that are expiring soon.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function expiringSoon(Request $request)
    {
        $days = $request->input('days', 30);
        
        $query = Product::with(['category', 'supplier'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('current_stock', '>', 0);
            
        // Search by name or code if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Sort by expiry date by default
        $sortField = $request->input('sort', 'expiry_date');
        $sortDirection = $request->input('direction', 'asc');
        
        $products = $query->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();
            
        return view('products.expiring', compact('products', 'days'));
    }
    
    /**
     * Check if product needs alerts and send notifications
     * 
     * @param Product $product
     * @param int|null $oldStock
     * @param string|null $oldExpiryDate
     * @return void
     */
    private function checkProductForAlerts(Product $product, $oldStock = null, $oldExpiryDate = null)
    {
        $alertsCreated = 0;
        
        // Check for low stock
        if ($product->current_stock <= $product->min_stock && $product->current_stock > 0) {
            // Only create alert if stock has changed to below minimum
            if ($oldStock === null || $oldStock > $product->min_stock) {
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
                    \Illuminate\Support\Facades\Log::info("Low stock email notification sent", [
                        'product_name' => $product->name,
                        'current_stock' => $product->current_stock,
                        'min_stock' => $product->min_stock,
                        'email' => 'marouanfolane@gmail.com'
                    ]);
                } catch (\Exception $e) {
                    // Detailed error logging
                    \Illuminate\Support\Facades\Log::error("Failed to send low stock email notification", [
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
            if ($oldStock === null || $oldStock > 0) {
                $this->createProductAlert(
                    $product,
                    'Out of Stock Alert',
                    "Product {$product->name} (SKU: {$product->code}) is out of stock. Please restock as soon as possible.",
                    'danger'
                );
                $alertsCreated++;
            }
        }
        
        // Check for expiry within 30 days
        if ($product->expiry_date && $product->current_stock > 0) {
            $expiryDate = \Carbon\Carbon::parse($product->expiry_date);
            $now = \Carbon\Carbon::now();
            
            if ($expiryDate->greaterThan($now) && $expiryDate->lessThanOrEqualTo($now->copy()->addDays(30))) {
                // Only create alert if expiry date has changed or is new
                if ($oldExpiryDate === null || $oldExpiryDate != $product->expiry_date) {
                    $daysRemaining = $now->diffInDays($expiryDate);
                    $this->createProductAlert(
                        $product,
                        'Product Expiring Soon',
                        "Product {$product->name} (SKU: {$product->code}) will expire in {$daysRemaining} days.",
                        'warning'
                    );
                    $alertsCreated++;
                }
            }
        }
        
        // Check for sensible categories and send immediate email notifications
        if ($product->category_id) {
            $sensibleCategory = \App\Models\SensibleCategory::where('category_id', $product->category_id)
                ->where('is_active', true)
                ->first();
                
            if ($sensibleCategory && $product->current_stock <= $sensibleCategory->min_quantity) {
                // Only send email if stock previously wasn't below the threshold or if this is the first check
                if ($oldStock === null || $oldStock > $sensibleCategory->min_quantity) {
                    try {
                        // Send immediate email notification
                        \Illuminate\Support\Facades\Mail::to($sensibleCategory->notification_email)
                            ->send(new \App\Mail\SensibleProductMail($product, $sensibleCategory->category));
                            
                        // Update last notification sent timestamp
                        $sensibleCategory->last_notification_sent = now();
                        $sensibleCategory->save();
                        
                        // Log the sent notification
                        \Illuminate\Support\Facades\Log::info("Immediate sensible category notification sent from ProductController", [
                            'category_id' => $sensibleCategory->category_id,
                            'category_name' => $sensibleCategory->category->name,
                            'product_name' => $product->name,
                            'current_stock' => $product->current_stock,
                            'min_quantity' => $sensibleCategory->min_quantity,
                            'email' => $sensibleCategory->notification_email
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send immediate sensible category notification from ProductController", [
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
            \Log::info('Product alert notification sent', [
                'alert_id' => $alert->id,
                'product_id' => $product->id,
                'recipients' => $users->count(),
                'phone_numbers' => $users->pluck('phone_number')->toArray()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send product alert notification', [
                'alert_id' => $alert->id,
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $alert;
    }
}
