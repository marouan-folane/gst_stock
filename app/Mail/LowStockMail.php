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
     * The products with low stock.
     *
     * @var Collection
     */
    public $products;

    /**
     * Create a new message instance.
     *
     * @param  Collection  $products
     * @return void
     */
    public function __construct(Collection $products)
    {
        $this->products = $products;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("ALERT: Low Stock Products - {$this->products->count()} Products Need Attention")
            ->markdown('emails.low-stock')
            ->with([
                'products' => $this->products,
                'count' => $this->products->count()
            ]);
    }
}
