@component('mail::message')
# Invitation to register

You have been invited to register and start using the {{ config('app.name') }}.
Click the link below and follow the instructions to get started.

@component('mail::button', ['url' => $invitation->link])
Register
@endcomponent

*The account is exclusively for your official use. DO NOT share your login details with anyone.

This invitation will expire in {{ config('chimera.invitation.ttl_hours') }} hours ({{$invitation->expires_at->toDayDateTimeString()}}).
Please make sure you register before then as the link will not work after that.*

Best regards,<br>
{{ config('app.name') }} Manager
@endcomponent
