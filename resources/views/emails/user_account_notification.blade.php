@component('mail::message')
# Welcome to NM Clockin

Hello {{ $credentials['name'] }},

Your account has been created successfully. Here are your login credentials:

@component('mail::panel')
Email: {{ $credentials['email'] }}
Password: {{ $credentials['password'] }}
@endcomponent

For security reasons, we recommend changing your password after your first login.

@component('mail::button', ['url' => config('app.url')])
Login to Your Account
@endcomponent

Best regards,<br>
{{ config('app.name') }}
@endcomponent