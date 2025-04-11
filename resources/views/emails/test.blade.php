@component('mail::message')
# Test Email

This is a test email from **{{ config('app.name') }}** to verify that your email settings are working correctly.

@component('mail::panel')
If you're receiving this email, your SMTP configuration is working properly!
@endcomponent

## Configuration Details:
- Mail Driver: {{ config('mail.default') }}
- Mail Host: {{ config('mail.mailers.smtp.host') }}
- Mail Port: {{ config('mail.mailers.smtp.port') }}
- From Address: {{ config('mail.from.address') }}

@component('mail::button', ['url' => config('app.url')])
Visit {{ config('app.name') }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent 