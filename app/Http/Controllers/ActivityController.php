<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a listing of the activities.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Activity::with('user');
        
        // Filter by type if provided
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by user if provided
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Get the activities with pagination
        $activities = $query->latest()->paginate(20);
        
        // Get unique activity types for the filter
        $activityTypes = Activity::select('type')->distinct()->pluck('type');
        
        return view('activities.index', compact('activities', 'activityTypes'));
    }
} 