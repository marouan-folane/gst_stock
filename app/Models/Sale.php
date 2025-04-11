<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_number',
        'customer_id',
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
     * Get the customer that owns the sale.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user that created the sale.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the sale.
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the payments for the sale.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the due amount for the sale.
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
     * Scope a query to only include sales with a specific payment status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Generate a unique invoice number.
     *
     * @return string
     */
    public static function generateInvoiceNumber()
    {
        $lastSale = self::latest()->first();
        $number = $lastSale ? (int)substr($lastSale->invoice_number, 4) + 1 : 1;
        
        return 'INV-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include sales for a specific customer.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $customerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to only include sales with a specific status.
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
     * Scope a query to only include sales for today.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Scope a query to only include sales for this week.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ]);
    }

    /**
     * Scope a query to only include sales for this month.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year);
    }

    /**
     * Scope a query to only include sales for this year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('date', Carbon::now()->year);
    }

    /**
     * Get formatted invoice number
     * 
     * @return string
     */
    public function getFormattedInvoiceNumberAttribute()
    {
        return 'INV-' . str_pad($this->invoice_number, 6, '0', STR_PAD_LEFT);
    }
} 