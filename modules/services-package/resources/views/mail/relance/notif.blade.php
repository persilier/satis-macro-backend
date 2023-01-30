@component('mail::message')
# Relance de {{$data["pilot"]}}

{{$data["message"]}}

Merci,<br>
{{ config('app.name') }}
@endcomponent
