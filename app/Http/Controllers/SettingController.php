<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $appName = config('app.name');
        $appEnv = config('app.env');
        $appDebug = config('app.debug');
        $emailSettings = [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
        
        $smsSettings = [
            'provider' => 'twilio',
            'from' => config('services.twilio.from'),
        ];
        
        // Get storage usage
        $diskUsage = $this->getDiskUsage();
        
        return view('settings.index', compact(
            'appName', 
            'appEnv', 
            'appDebug', 
            'emailSettings',
            'smsSettings',
            'diskUsage'
        ));
    }
    
    /**
     * Update the application settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_from_address' => 'required|email|max:255',
            'twilio_from' => 'nullable|string|max:20',
        ]);
        
        // Update .env file
        $this->updateEnvValue('APP_NAME', '"'.$validated['app_name'].'"');
        $this->updateEnvValue('MAIL_FROM_NAME', '"'.$validated['mail_from_name'].'"');
        $this->updateEnvValue('MAIL_FROM_ADDRESS', $validated['mail_from_address']);
        
        if (isset($validated['twilio_from'])) {
            $this->updateEnvValue('TWILIO_FROM', $validated['twilio_from']);
        }
        
        // Clear config cache
        Artisan::call('config:clear');
        
        return back()->with('success', 'Settings updated successfully.');
    }
    
    /**
     * Display the notification settings page.
     *
     * @return \Illuminate\Http\Response
     */
    public function showNotifications()
    {
        // Get stored notification settings
        $settings = json_decode(DB::table('settings')->where('key', 'notification_settings')->value('value') ?? '{}', true) ?: [];
        
        return view('settings.notifications', compact('settings'));
    }
    
    /**
     * Update notification settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateNotifications(Request $request)
    {
        $settings = [
            'notify_low_stock' => $request->has('notify_low_stock'),
            'notify_out_of_stock' => $request->has('notify_out_of_stock'),
            'notify_expiring' => $request->has('notify_expiring'),
            'expiry_days_threshold' => $request->input('expiry_days_threshold', 30),
            'notify_admin' => $request->has('notify_admin'),
            'notify_manager' => $request->has('notify_manager'),
            'notify_employee' => $request->has('notify_employee'),
            'additional_emails' => $request->input('additional_emails'),
            'email_schedule' => $request->input('email_schedule', 'immediate'),
        ];
        
        // Save settings to database
        DB::table('settings')->updateOrInsert(
            ['key' => 'notification_settings'],
            ['value' => json_encode($settings), 'updated_at' => now()]
        );
        
        return back()->with('success', 'Notification settings updated successfully.');
    }
    
    /**
     * Send a test email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        
        try {
            Mail::to($request->email)->send(new TestMail());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get disk usage statistics
     *
     * @return array
     */
    private function getDiskUsage()
    {
        $diskTotal = disk_total_space(storage_path());
        $diskFree = disk_free_space(storage_path());
        $diskUsed = $diskTotal - $diskFree;
        
        return [
            'total' => $this->formatBytes($diskTotal),
            'used' => $this->formatBytes($diskUsed),
            'free' => $this->formatBytes($diskFree),
            'percent' => round(($diskUsed / $diskTotal) * 100, 2)
        ];
    }
    
    /**
     * Format bytes to human readable format
     *
     * @param  int  $bytes
     * @param  int  $precision
     * @return string
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Update a value in the .env file
     *
     * @param  string  $key
     * @param  string  $value
     * @return bool
     */
    private function updateEnvValue($key, $value)
    {
        $path = app()->environmentFilePath();
        $content = file_get_contents($path);
        
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        
        return file_put_contents($path, $content);
    }
}
