<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\User;
use App\Models\Customer;
use App\Notifications\StockAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use App\Helpers\SmsHelper;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    /**
     * Show the form to send an SMS
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sms.index');
    }
    
    /**
     * Send an SMS to a specific number
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'message' => 'required|string|max:160',
            'provider' => 'required|in:infobip,msg91',
        ]);
        
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $phoneNumber = $request->input('phone_number');
        $message = $request->input('message');
        $provider = $request->input('provider');
        
        $success = false;
        
        if ($provider === 'infobip') {
            $success = SmsHelper::sendInfobipSms($phoneNumber, $message);
        } else {
            $success = SmsHelper::sendMsg91Sms($phoneNumber, $message);
        }
        
        if ($success) {
            return back()->with('success', 'SMS sent successfully via ' . strtoupper($provider));
        } else {
            return back()->with('error', 'Failed to send SMS. Please try again later.');
        }
    }
    
    /**
     * Send an SMS notification to a user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendToUser(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:160',
        ]);
        
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = \App\Models\User::find($request->input('user_id'));
        $message = $request->input('message');
        
        if (!$user->phone_number) {
            return back()->with('error', 'User does not have a phone number.');
        }
        
        $success = SmsHelper::sendSms($user->phone_number, $message);
        
        if ($success) {
            return back()->with('success', 'SMS sent successfully to ' . $user->name);
        } else {
            return back()->with('error', 'Failed to send SMS. Please try again later.');
        }
    }

    /**
     * Send a direct SMS message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendDirectSms(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string|max:160',
        ]);

        try {
            // Get the Twilio client instance
            $twilio = app('twilio');

            // Send message
            $message = $twilio->messages->create(
                $request->phone_number,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $request->message
                ]
            );

            // Log the successful SMS
            \Log::info('SMS sent successfully', [
                'to' => $request->phone_number,
                'message_sid' => $message->sid,
                'status' => $message->status,
            ]);

            return back()->with('success', 'SMS sent successfully!');
        } catch (TwilioException $e) {
            \Log::error('Failed to send SMS', [
                'error' => $e->getMessage(),
                'phone' => $request->phone_number
            ]);
            
            $errorMsg = $e->getMessage();
            $helpMessage = '';
            
            // Check for trial account verification error
            if (strpos($errorMsg, 'unverified') !== false) {
                $phone = preg_replace('/[^0-9+]/', '', $request->phone_number);
                $helpMessage = sprintf(
                    '<br><strong>Trial Account Limitation:</strong> You need to verify this number first at <a href="https://twilio.com/user/account/phone-numbers/verified" target="_blank">Twilio Verified Numbers</a> or upgrade your Twilio account to paid.'
                );
            }
            // Check for geographic restrictions
            elseif (strpos($errorMsg, 'current combination of') !== false && strpos($errorMsg, 'parameters') !== false) {
                $helpMessage = sprintf(
                    '<br><strong>Geographic Restriction:</strong> Your Twilio phone number (+%s) cannot send to this destination country. Please <a href="https://www.twilio.com/console/sms/settings/geo-permissions" target="_blank">update your Twilio Geographic Permissions</a> or <a href="https://www.twilio.com/console/phone-numbers/incoming" target="_blank">purchase a phone number</a> for the destination region.',
                    substr(config('services.twilio.from'), 1)
                );
            }
            
            return back()->with('error', 'Failed to send SMS: ' . $errorMsg . $helpMessage);
        }
    }

    /**
     * Show the SMS form
     * 
     * @return \Illuminate\Http\Response
     */
    public function showSmsForm()
    {
        // Check if using a Twilio trial account
        $isTrial = false;
        $twilioNumber = config('services.twilio.from');
        $twilioNumberCountry = $this->getCountryFromPhone($twilioNumber);
        
        try {
            $twilio = app('twilio');
            $account = $twilio->api->v2010->accounts(config('services.twilio.sid'))->fetch();
            $isTrial = $account->type === 'Trial';
        } catch (\Exception $e) {
            \Log::error('Failed to check Twilio account type', [
                'error' => $e->getMessage()
            ]);
        }
        
        return view('sms.form', compact('isTrial', 'twilioNumber', 'twilioNumberCountry'));
    }
    
    /**
     * Get country code from phone number
     * 
     * @param string $phoneNumber
     * @return string
     */
    private function getCountryFromPhone($phoneNumber)
    {
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        if (strpos($phoneNumber, '+1') === 0) {
            return 'US/Canada';
        } elseif (strpos($phoneNumber, '+44') === 0) {
            return 'UK';
        } elseif (strpos($phoneNumber, '+212') === 0) {
            return 'Morocco';
        } elseif (strpos($phoneNumber, '+33') === 0) {
            return 'France';
        } elseif (strpos($phoneNumber, '+34') === 0) {
            return 'Spain';
        } elseif (strpos($phoneNumber, '+91') === 0) {
            return 'India';
        } elseif (strpos($phoneNumber, '+61') === 0) {
            return 'Australia';
        } else {
            // Parse the first 3 digits after + to guess country code
            preg_match('/^\+(\d{1,3})/', $phoneNumber, $matches);
            if (isset($matches[1])) {
                return 'Country code +' . $matches[1];
            }
            return 'Unknown';
        }
    }

    /**
     * Send SMS notifications for an alert to all administrators.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendAlertSms(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'message' => 'required|string|max:160',
            'type' => 'required|in:info,warning,danger',
            'product_id' => 'nullable|exists:products,id',
        ]);

        try {
            // Create an alert
            $alert = Alert::create([
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'product_id' => $request->product_id,
                'is_read' => false,
            ]);

            // Get users with admin or manager roles who have phone numbers
            $users = User::whereIn('role', ['admin', 'manager'])
                ->whereNotNull('phone_number')
                ->get();
                
            // If no users have phone numbers, create a default user with the default phone number
            if ($users->isEmpty() && env('DEFAULT_SMS_RECIPIENT')) {
                $defaultUser = new User();
                $defaultUser->phone_number = env('DEFAULT_SMS_RECIPIENT');
                $users = collect([$defaultUser]);
            }
            
            $sentCount = 0;
            $failedCount = 0;
            $unverifiedNumbers = [];
            $geographicRestrictions = [];
            
            // Send notifications to each user individually to handle failures better
            foreach ($users as $user) {
                try {
                    $user->notify(new StockAlertNotification($alert));
                    $sentCount++;
                } catch (TwilioException $e) {
                    $failedCount++;
                    $errorMsg = $e->getMessage();
                    
                    if (strpos($errorMsg, 'unverified') !== false) {
                        $unverifiedNumbers[] = $user->phone_number;
                    } elseif (strpos($errorMsg, 'current combination of') !== false && strpos($errorMsg, 'parameters') !== false) {
                        $geographicRestrictions[] = $user->phone_number;
                    }
                    
                    \Log::error('Failed to send alert SMS to user', [
                        'user_id' => $user->id ?? 'default',
                        'phone' => $user->phone_number,
                        'error' => $errorMsg
                    ]);
                }
            }
            
            $message = "Alert created successfully. SMS sent to {$sentCount} recipients.";
            
            if ($failedCount > 0) {
                $message .= " Failed to send to {$failedCount} recipients.";
                
                if (!empty($unverifiedNumbers) && count($unverifiedNumbers) <= 3) {
                    $message .= " Unverified numbers: " . implode(', ', $unverifiedNumbers);
                    $message .= ". <a href='https://twilio.com/user/account/phone-numbers/verified' target='_blank'>Verify these numbers</a> or upgrade your Twilio account.";
                } elseif (!empty($unverifiedNumbers)) {
                    $message .= " Several unverified numbers detected. Please verify them in your Twilio account.";
                }
                
                if (!empty($geographicRestrictions)) {
                    $message .= " Geographic restrictions for: " . implode(', ', $geographicRestrictions);
                    $message .= ". <a href='https://www.twilio.com/console/sms/settings/geo-permissions' target='_blank'>Check your Twilio Geographic Permissions</a>.";
                }
            }
            
            // Log notification
            \Log::info('Alert SMS notification attempt', [
                'alert_id' => $alert->id,
                'total_recipients' => $users->count(),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'unverified_numbers' => $unverifiedNumbers,
                'geographic_restrictions' => $geographicRestrictions
            ]);

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Failed to send alert SMS', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to send SMS alert: ' . $e->getMessage());
        }
    }

    /**
     * Send a bulk SMS to all customers or selected customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendBulkSms(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:160',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        try {
            $twilio = app('twilio');
            $sent = 0;
            $failed = 0;
            $unverifiedNumbers = [];
            $geographicRestrictions = [];
            $customers = [];

            // If customer_ids is provided, get those specific customers
            if ($request->has('customer_ids') && !empty($request->customer_ids)) {
                $customers = Customer::whereIn('id', $request->customer_ids)
                    ->whereNotNull('phone')
                    ->get();
            } else {
                // Otherwise, get all customers with phone numbers
                $customers = Customer::whereNotNull('phone')->get();
            }
            
            // Check if using trial account
            $isTrial = false;
            try {
                $account = $twilio->api->v2010->accounts(config('services.twilio.sid'))->fetch();
                $isTrial = $account->type === 'Trial';
            } catch (\Exception $e) {
                // Ignore error, assume it's not a trial
            }

            foreach ($customers as $customer) {
                try {
                    $message = $twilio->messages->create(
                        $customer->phone,
                        [
                            'from' => config('services.twilio.from'),
                            'body' => $request->message
                        ]
                    );
                    $sent++;
                } catch (TwilioException $e) {
                    $failed++;
                    $errorMsg = $e->getMessage();
                    
                    if (strpos($errorMsg, 'unverified') !== false) {
                        $unverifiedNumbers[] = $customer->phone;
                    } elseif (strpos($errorMsg, 'current combination of') !== false && strpos($errorMsg, 'parameters') !== false) {
                        $geographicRestrictions[] = $customer->phone;
                    }
                    
                    \Log::error('Failed to send bulk SMS to customer', [
                        'customer_id' => $customer->id,
                        'phone' => $customer->phone,
                        'error' => $errorMsg
                    ]);
                }
            }
            
            $message = "Bulk SMS: {$sent} sent successfully, {$failed} failed.";
            
            if ($failed > 0) {
                if ($isTrial && count($unverifiedNumbers) > 0 && count($unverifiedNumbers) <= 5) {
                    $message .= " Unverified numbers: " . implode(', ', $unverifiedNumbers);
                    $message .= ". <a href='https://twilio.com/user/account/phone-numbers/verified' target='_blank'>Verify these numbers</a> or upgrade your Twilio account.";
                } elseif ($isTrial && count($unverifiedNumbers) > 5) {
                    $message .= " Trial accounts require phone number verification. Please upgrade your Twilio account or verify recipient numbers.";
                }
                
                if (count($geographicRestrictions) > 0 && count($geographicRestrictions) <= 5) {
                    $message .= " Geographic restrictions for: " . implode(', ', $geographicRestrictions);
                    $message .= ". <a href='https://www.twilio.com/console/sms/settings/geo-permissions' target='_blank'>Check your Twilio Geographic Permissions</a>.";
                } elseif (count($geographicRestrictions) > 5) {
                    $message .= " Several geographic restrictions detected. Your Twilio phone number may not be able to send to these countries.";
                }
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Failed to process bulk SMS', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to send bulk SMS: ' . $e->getMessage());
        }
    }
} 