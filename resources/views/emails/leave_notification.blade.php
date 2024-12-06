@component('mail::message')
# Leave Notification

Hello {{ $role }},

A leave request has been created:

- **Leave ID:** {{ $leave->id }}
- **Requested By:** {{ $leave->user->firstName }} {{ $leave->user->lastName }}
- **Start Date:** {{ $leave->start }}
- **End Date:** {{ $leave->end }}
- **Reason:** {{ $leave->reason }}

{{-- @component('mail::button', ['url' => url('/leaves/' . $leave->id)]) --}}
View Leave Details
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent
