<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'notes',
        'supplier_id',
        'customer_id',
        'reference_number',
    ];

    /**
     * Get the product that owns the stock movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that owns the stock movement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supplier that owns the stock movement.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the customer that owns the stock movement.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
