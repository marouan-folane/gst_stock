<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Activity;
use App\Models\StockMovement;
use App\Models\Alert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Count totals for stats cards
        $totalProducts = Product::count();
        $totalSuppliers = Supplier::count();
        $totalCustomers = Customer::count();
        
        // Sales statistics
        $todaySales = Sale::whereDate('date', Carbon::today())
            ->where('status', 'completed')
            ->sum('total_amount');
        
        $monthlySales = Sale::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');
        
        // Orders statistics
        $pendingOrders = Sale::where('status', 'pending')->count();
        $totalOrders = Sale::count();
        
        // Low stock products
        $lowStockProducts = Product::where('current_stock', '<=', DB::raw('min_stock'))
            ->where('current_stock', '>', 0)
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get();
            
        $lowStockCount = Product::where('current_stock', '<=', DB::raw('min_stock'))
            ->where('current_stock', '>', 0)
            ->count();
            
        // Top selling products
        $topSellingProducts = Product::select(
                'products.id', 'products.name', 'products.code', 'products.description',
                'products.category_id', 'products.supplier_id', 'products.cost_price',
                'products.selling_price', 'products.current_stock', 'products.min_stock', 
                'products.max_stock', 'products.expiry_date', 'products.unit', 
                'products.image', 'products.is_active', 'products.created_at', 'products.updated_at',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total) as total_revenue')
            )
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where(function($query) {
                $query->where('sales.status', 'completed')
                      ->orWhereNull('sales.status'); // Include products with no sales
            })
            ->groupBy(
                'products.id', 'products.name', 'products.code', 'products.description',
                'products.category_id', 'products.supplier_id', 'products.cost_price',
                'products.selling_price', 'products.current_stock', 'products.min_stock', 
                'products.max_stock', 'products.expiry_date', 'products.unit', 
                'products.image', 'products.is_active', 'products.created_at', 'products.updated_at'
            )
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Get recent stock movements
        $recentStockMovements = StockMovement::with(['product', 'user'])
            ->latest()
            ->limit(10)
            ->get();
            
        // Get recent alerts
        $recentAlerts = Alert::latest()
            ->limit(5)
            ->get();
            
        // Get products for stock overview
        $products = Product::with('category')
            ->orderBy('current_stock', 'asc')
            ->limit(10)
            ->get();
            
        // Recent activities
        $recentActivities = Activity::latest()
            ->limit(10)
            ->get();
            
        // Sales chart data
        $salesChartData = $this->getSalesChartData();
        
        // Category data for pie chart
        $categoryData = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->products_count
                ];
            });
            
        // Category colors for pie chart
        $colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
            '#5a5c69', '#6610f2', '#fd7e14', '#20c9a6', '#858796'
        ];
        
        $hoverColors = [
            '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', 
            '#484a54', '#4609ac', '#c96a17', '#169b80', '#60616f'
        ];
        
        $categoryColors = [];
        foreach ($categoryData as $index => $category) {
            $colorIndex = $index % count($colors);
            $categoryColors[] = [
                'name' => $category['name'],
                'color' => $colors[$colorIndex],
                'hover' => $hoverColors[$colorIndex]
            ];
        }
            
        return view('dashboard', compact(
            'totalProducts',
            'totalSuppliers',
            'totalCustomers',
            'todaySales',
            'monthlySales',
            'pendingOrders',
            'totalOrders',
            'lowStockCount',
            'lowStockProducts',
            'topSellingProducts',
            'recentActivities',
            'salesChartData',
            'categoryData',
            'categoryColors',
            'recentStockMovements',
            'recentAlerts',
            'products'
        ));
    }
    
    /**
     * Get sales chart data for the last 30 days
     *
     * @return array
     */
    private function getSalesChartData()
    {
        $days = 30;
        $dateFormat = 'M d';
        
        $dates = [];
        $sales = [];

        // Generate dates for the past 30 days
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format($dateFormat);
            
            $dailySales = Sale::whereDate('date', $date->format('Y-m-d'))
                ->where('status', 'completed')
                ->sum('total_amount');
                
            $sales[] = $dailySales;
        }

        return [
            'labels' => $dates,
            'values' => $sales
        ];
    }
}
