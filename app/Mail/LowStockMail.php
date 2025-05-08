<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The products with stock issues.
     *
     * @var Collection
     */
    public $products;

    /**
     * The type of stock alert (low or max)
     * 
     * @var string
     */
    public $alertType;

    /**
     * Create a new message instance.
     *
     * @param  Collection  $products
     * @param  string  $alertType  Either 'low' or 'max'
     * @return void
     */
    public function __construct(Collection $products, $alertType = 'low')
    {
        $this->products = $products;
        $this->alertType = $alertType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "ALERT: Low Stock Products - {$this->products->count()} Products Need Attention";
        $view = 'emails.low-stock';
        
        if ($this->alertType === 'max') {
            $subject = "ALERT: Maximum Stock Exceeded - {$this->products->count()} Products Over Maximum";
            $view = 'emails.max-stock';
        }
        
        // Fallback to low-stock template if max-stock template doesn't exist
        if ($this->alertType === 'max' && !view()->exists($view)) {
            $view = 'emails.low-stock';
        }
        
        return $this->subject($subject)
            ->markdown($view)
            ->with([
                'products' => $this->products,
                'count' => $this->products->count(),
                'alertType' => $this->alertType
            ]);
    }
}
