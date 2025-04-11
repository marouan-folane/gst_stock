<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'description',
        'user_id',
        'subject_id',
        'subject_type',
        'link',
        'properties',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject of the activity.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get all activities for a specific user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function forUser($userId)
    {
        return static::where('user_id', $userId)->latest()->get();
    }

    /**
     * Record a new activity
     *
     * @param string $type
     * @param string $description
     * @param Model|null $subject
     * @param array $properties
     * @param string|null $link
     * @return Activity
     */
    public static function log($type, $description, $subject = null, $properties = [], $link = null)
    {
        return static::create([
            'type' => $type,
            'description' => $description,
            'user_id' => auth()->id(),
            'subject_id' => $subject ? $subject->id : null,
            'subject_type' => $subject ? get_class($subject) : null,
            'properties' => $properties,
            'link' => $link,
        ]);
    }
} 