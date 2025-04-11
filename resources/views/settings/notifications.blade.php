@extends('layouts.app')

@section('title', 'Email Notification Settings')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Email Notification Settings</h1>
    <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-rounded">
        <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Settings
    </a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Email Alert Configuration</h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('settings.notifications.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <h5>Stock Alert Settings</h5>
                        <p class="text-muted">Configure which stock alerts will trigger email notifications.</p>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_low_stock" name="notify_low_stock" value="1" {{ $settings['notify_low_stock'] ?? true ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_low_stock">
                                Low Stock Alerts
                            </label>
                            <small class="form-text text-muted d-block">Send email when product stock falls below minimum level</small>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_out_of_stock" name="notify_out_of_stock" value="1" {{ $settings['notify_out_of_stock'] ?? true ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_out_of_stock">
                                Out of Stock Alerts
                            </label>
                            <small class="form-text text-muted d-block">Send email when product goes out of stock</small>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_expiring" name="notify_expiring" value="1" {{ $settings['notify_expiring'] ?? true ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_expiring">
                                Expiring Products Alerts
                            </label>
                            <small class="form-text text-muted d-block">Send email when products are near expiry date</small>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="expiry_days_threshold" class="form-label">Expiry Warning Days</label>
                            <input type="number" class="form-control" id="expiry_days_threshold" name="expiry_days_threshold" value="{{ $settings['expiry_days_threshold'] ?? 30 }}" min="1" max="90">
                            <small class="form-text text-muted">Send alerts when products are this many days from expiring</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Recipients</h5>
                        <p class="text-muted">Choose which user roles will receive email alerts.</p>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_admin" name="notify_admin" value="1" {{ $settings['notify_admin'] ?? true ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_admin">
                                Administrators
                            </label>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_manager" name="notify_manager" value="1" {{ $settings['notify_manager'] ?? true ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_manager">
                                Managers
                            </label>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_employee" name="notify_employee" value="1" {{ $settings['notify_employee'] ?? false ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_employee">
                                Employees
                            </label>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="additional_emails" class="form-label">Additional Email Recipients</label>
                            <textarea class="form-control" id="additional_emails" name="additional_emails" rows="2" placeholder="Enter comma-separated email addresses">{{ $settings['additional_emails'] ?? '' }}</textarea>
                            <small class="form-text text-muted">Add external email addresses that should receive notifications</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Email Schedule</h5>
                        <p class="text-muted">Configure when and how often email alerts are sent.</p>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" id="schedule_immediate" name="email_schedule" value="immediate" {{ ($settings['email_schedule'] ?? 'immediate') == 'immediate' ? 'checked' : '' }}>
                            <label class="form-check-label" for="schedule_immediate">
                                Send Immediately
                            </label>
                            <small class="form-text text-muted d-block">Send email alerts as soon as they are triggered</small>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" id="schedule_daily" name="email_schedule" value="daily" {{ ($settings['email_schedule'] ?? 'immediate') == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label" for="schedule_daily">
                                Daily Digest
                            </label>
                            <small class="form-text text-muted d-block">Send a single daily email with all alerts</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Test Email</h5>
                        <p class="text-muted">Send a test email to verify your settings.</p>
                        
                        <div class="form-group mb-3">
                            <label for="test_email" class="form-label">Test Email Address</label>
                            <div class="input-group">
                                <input type="email" class="form-control" id="test_email" name="test_email" placeholder="Enter email address">
                                <button type="button" id="send_test_email" class="btn btn-outline-primary">Send Test</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Email Configuration Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5 class="font-weight-bold text-primary">SMTP Settings</h5>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Mail Provider:</span>
                        <span class="font-weight-bold">{{ config('mail.mailer') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Mail Host:</span>
                        <span class="font-weight-bold">{{ config('mail.host') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Mail Port:</span>
                        <span class="font-weight-bold">{{ config('mail.port') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">From Address:</span>
                        <span class="font-weight-bold">{{ config('mail.from.address') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">From Name:</span>
                        <span class="font-weight-bold">{{ config('mail.from.name') }}</span>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6 class="font-weight-bold">How to set up Gmail SMTP</h6>
                    <ol class="ps-3 mb-0">
                        <li>Make sure you have <strong>Less secure app access</strong> enabled or use an App Password</li>
                        <li>Update the <code>.env</code> file with your Gmail credentials</li>
                        <li>Test the connection using the Test Email feature</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('send_test_email').addEventListener('click', function() {
        const testEmail = document.getElementById('test_email').value;
        if (!testEmail) {
            alert('Please enter an email address for testing');
            return;
        }
        
        fetch('{{ route("settings.notifications.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ email: testEmail })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Test email sent successfully!');
            } else {
                alert('Failed to send test email: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    });
</script>
@endsection 