@component('mail::message')
# Bonjour M/MME {{ $name }},

{{ $text }}

@if ($files)
@foreach ($files as $file)
<a href="{{url($file->url)}}">{{$file->title}}</a>
@endforeach
@endif

@endcomponent
