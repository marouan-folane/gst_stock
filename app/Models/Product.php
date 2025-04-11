<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'category_id',
        'supplier_id',
        'cost_price',
        'selling_price',
        'current_stock',
        'min_stock',
        'max_stock',
        'expiry_date',
        'unit',
        'image',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the supplier that owns the product.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the stock movements for the product.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the alerts for the product.
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Check if the product is low in stock.
     *
     * @return bool
     */
    public function isLowStock()
    {
        return $this->current_stock <= $this->min_stock;
    }

    /**
     * Check if the product is out of stock.
     *
     * @return bool
     */
    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }

    /**
     * Check if the product is approaching its expiry date.
     * 
     * @param int $days Days until expiration to check
     * @return bool
     */
    public function isExpiringSoon($days = 30)
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        $now = \Carbon\Carbon::now();
        $expiryDate = \Carbon\Carbon::parse($this->expiry_date);
        
        return $now->lte($expiryDate) && $now->diffInDays($expiryDate) <= $days;
    }
}
