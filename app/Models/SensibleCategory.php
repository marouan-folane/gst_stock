<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensibleCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'min_quantity',
        'notification_email',
        'notification_frequency',
        'is_active',
        'last_notification_sent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'min_quantity' => 'integer',
        'last_notification_sent' => 'datetime',
    ];

    /**
     * Get the category associated with the sensible category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all products belonging to this sensible category.
     */
    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            Category::class,
            'id', // Foreign key on categories table
            'category_id', // Foreign key on products table
            'category_id', // Local key on sensible_categories table
            'id' // Local key on categories table
        );
    }
}
