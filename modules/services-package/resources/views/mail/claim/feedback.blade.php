@component('mail::header')
 # {{env('APP_NAME')}}
@endcomponent
@component('mail::message')
# Bonjour M/MME {{ $name }},

{{ $text }}

@endcomponent
