<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;

class SensibleProductMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The products that triggered the notification.
     *
     * @var Collection|Product
     */
    public $products;

    /**
     * The category (if notification is for a category).
     *
     * @var Category|null
     */
    public $category;

    /**
     * Create a new message instance.
     *
     * @param  Collection|Product  $products
     * @param  Category|null  $category
     * @return void
     */
    public function __construct($products, Category $category = null)
    {
        // Convert single product to collection for consistent template handling
        if ($products instanceof Product) {
            $products = collect([$products]);
        }

        $this->products = $products;
        $this->category = $category;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->category
            ? "ALERT: Low Stock in {$this->category->name} Category"
            : "ALERT: Sensible Product Stock Alert";

        return $this->subject($subject)
            ->markdown('emails.sensible-product')
            ->with([
                'products' => $this->products,
                'category' => $this->category,
                'count' => $this->products->count()
            ]);
    }
}
