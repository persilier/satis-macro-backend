@component('mail::message')
# Bonjour M/MME {{ $name }},

{{ $text }}

@if ($files)
@foreach ($files as $file)
<a href="{{$file}}"></a>
@endforeach
@endif

@endcomponent
