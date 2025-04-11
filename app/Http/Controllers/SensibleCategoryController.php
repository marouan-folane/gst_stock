<?php

namespace App\Http\Controllers;

use App\Models\SensibleCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SensibleCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sensibleCategories = SensibleCategory::with('category')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('sensible-categories.index', compact('sensibleCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('name')
            ->whereNotIn('id', function($query) {
                $query->select('category_id')->from('sensible_categories');
            })
            ->get();
            
        if ($categories->isEmpty()) {
            return redirect()->route('sensible-categories.index')
                ->with('error', 'All categories have already been designated as sensible. Deactivate or delete existing ones first.');
        }
        
        return view('sensible-categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'min_quantity' => 'required|integer|min:1',
            'notification_email' => 'required|email',
            'notification_frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        SensibleCategory::create($validated);

        return redirect()->route('sensible-categories.index')
            ->with('success', 'Monitored category created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SensibleCategory  $sensibleCategory
     * @return \Illuminate\Http\Response
     */
    public function show(SensibleCategory $sensibleCategory)
    {
        $sensibleCategory->load('category');
        
        // Get current stock level if available
        // This is a placeholder - implement the actual stock retrieval logic
        $currentStock = null;
        if ($sensibleCategory->category) {
            // Example: Get current stock from inventory system
            // $currentStock = InventoryService::getStockLevel($sensibleCategory->category_id);
            $currentStock = 0; // Placeholder value
        }
        
        // Get recent notifications for this category if available
        // This is a placeholder - implement the actual notification retrieval
        $notifications = []; // Placeholder for notifications
        
        return view('sensible-categories.show', compact('sensibleCategory', 'currentStock', 'notifications'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SensibleCategory  $sensibleCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(SensibleCategory $sensibleCategory)
    {
        $categories = Category::all();
        return view('sensible-categories.edit', compact('sensibleCategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SensibleCategory  $sensibleCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SensibleCategory $sensibleCategory)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'min_quantity' => 'required|integer|min:1',
            'notification_email' => 'required|email',
            'notification_frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $sensibleCategory->update($validated);

        return redirect()->route('sensible-categories.index')
            ->with('success', 'Monitored category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SensibleCategory  $sensibleCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(SensibleCategory $sensibleCategory)
    {
        $sensibleCategory->delete();
        
        return redirect()->route('sensible-categories.index')
            ->with('success', 'Monitored category deleted successfully.');
    }
    
    /**
     * Toggle the active status of the specified resource.
     *
     * @param  \App\Models\SensibleCategory  $sensibleCategory
     * @return \Illuminate\Http\Response
     */
    public function toggleActive(SensibleCategory $sensibleCategory)
    {
        $sensibleCategory->is_active = !$sensibleCategory->is_active;
        $sensibleCategory->save();
        
        $status = $sensibleCategory->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('sensible-categories.index')
            ->with('success', "Sensible category {$status} successfully");
    }
    
    /**
     * Send a test notification for the category.
     *
     * @param  \App\Models\SensibleCategory  $sensibleCategory
     * @return \Illuminate\Http\Response
     */
    public function testNotification(SensibleCategory $sensibleCategory)
    {
        // Get some products from the category
        $products = $sensibleCategory->category->products()->limit(3)->get();
        
        if ($products->isEmpty()) {
            return redirect()->route('sensible-categories.show', $sensibleCategory)
                ->with('error', 'Cannot send test notification: No products found in this category');
        }
        
        // Send the test email
        try {
            \Mail::to($sensibleCategory->notification_email)
                ->send(new \App\Mail\SensibleProductMail($products, $sensibleCategory->category));
                
            return redirect()->route('sensible-categories.show', $sensibleCategory)
                ->with('success', 'Test notification sent successfully to ' . $sensibleCategory->notification_email);
        } catch (\Exception $e) {
            return redirect()->route('sensible-categories.show', $sensibleCategory)
                ->with('error', 'Failed to send test notification: ' . $e->getMessage());
        }
    }
}
