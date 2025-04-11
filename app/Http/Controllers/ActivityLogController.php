<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Activity::with('user');
        
        // Filter by type if specified
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }
        
        // Filter by date range if specified
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get unique activity types for the filter dropdown
        $activityTypes = Activity::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
        
        $activities = $query->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('activities.index', compact('activities', 'activityTypes'));
    }
}
