<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'amount',
        'method',
        'reference',
        'date',
        'user_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
    ];

    /**
     * Get the sale that owns the payment.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the user that created the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Process payment and update sale payment status.
     */
    public function process()
    {
        // Update sale paid amount
        $sale = $this->sale;
        $sale->paid_amount += $this->amount;
        $sale->save();
        
        // Update payment status
        $sale->updatePaymentStatus();
    }
} 