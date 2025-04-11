<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Purchase extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reference_no',
        'supplier_id',
        'user_id',
        'date',
        'total_amount',
        'paid_amount',
        'discount',
        'tax',
        'status',
        'payment_status',
        'payment_method',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    /**
     * Get the supplier that owns the purchase.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user that created the purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the purchase.
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the payments for the purchase.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the due amount for the purchase.
     *
     * @return float
     */
    public function getDueAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Update payment status based on paid amount.
     */
    public function updatePaymentStatus()
    {
        if ($this->paid_amount <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($this->paid_amount < $this->total_amount) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'paid';
        }
        $this->save();
    }

    /**
     * Generate a unique reference number.
     *
     * @return string
     */
    public static function generateReferenceNumber()
    {
        $lastPurchase = self::latest()->first();
        $number = $lastPurchase ? (int)substr($lastPurchase->reference_no, 4) + 1 : 1;
        
        return 'PUR-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include purchases for a specific supplier.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $supplierId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope a query to only include purchases with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include purchases with a specific payment status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }
} 