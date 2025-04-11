<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display the profile edit form.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
            }
            
            $user->password = Hash::make($validated['password']);
        }
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'];
        $user->save();
        
        return back()->with('success', 'Profile updated successfully.');
    }
    
    /**
     * Update the user's notification preferences.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateNotificationSettings(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'receive_sms' => 'boolean',
        ]);
        
        // If user wants to receive SMS but hasn't provided a phone number
        if ($validated['receive_sms'] && empty($user->phone_number)) {
            return back()->withErrors(['phone_number' => 'You must provide a phone number to receive SMS notifications.']);
        }
        
        // Here you would update user notification preferences in the database
        // For now we're just using the phone_number field presence to determine if they receive SMS
        
        return back()->with('success', 'Notification preferences updated successfully.');
    }
}
