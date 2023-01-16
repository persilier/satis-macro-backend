@component('mail::message')
# Réaffection de la plaite {{$data["claim_reference"]}} par {{$data["pilot"]}}

Motif : <br>
{{$data["message"]}}

Merci,<br>
{{ config('app.name') }}
@endcomponent
