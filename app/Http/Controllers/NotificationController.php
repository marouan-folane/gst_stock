<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $alerts = Alert::with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('alerts.index', compact('alerts'));
    }
    
    /**
     * Mark a notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsRead($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->is_read = true;
        $alert->save();
        
        return back()->with('success', 'Notification marked as read');
    }
    
    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead()
    {
        Alert::where('is_read', false)
            ->update(['is_read' => true]);
            
        return back()->with('success', 'All notifications marked as read');
    }
}
