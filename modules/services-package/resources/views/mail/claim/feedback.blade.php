@component('mail::message')
# Bonjour M/MME {{ $name }},

{{ $text }}

@if (count($files) > 0)
@foreach ($files as $file)
<a href="{{url($file->url)}}">{{$file->title}}</a>
@endforeach
@endif

@endcomponent
