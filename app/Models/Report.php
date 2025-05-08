<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'parameters',
        'url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'parameters' => 'array',
    ];

    /**
     * Get the user who generated the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the URL for viewing the report.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        // Reconstruct the URL for the report based on type and parameters
        $url = route('reports.generate', array_merge(['type' => $this->type], $this->parameters ?: []));
        return $url;
    }
    
    /**
     * Get the download URL for the report.
     *
     * @return string
     */
    public function getDownloadUrlAttribute()
    {
        return route('reports.download', $this->id);
    }
} 