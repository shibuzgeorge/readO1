@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($dashboardLink)
<?php
switch ($level) {
    case 'success':
    case 'error':
        $color = $level;
        break;
    default:
        $color = 'primary';
}
?>
@component('mail::button', ['url' => $dashboardLink, 'color' => $color])
{{ $actionText1 }}
@endcomponent
@endisset

{{-- Action Button --}}
@isset($libraryLink)
<?php
switch ($level) {
    case 'success':
    case 'error':
        $color = $level;
        break;
    default:
        $color = 'primary';
}
?>
@component('mail::button', ['url' => $libraryLink,  'color' => $color])
{{ $actionText2 }}
@endcomponent
@endisset


{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards'),<br>
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($dashboardLink, $libraryLink)
@slot('subcopy')
@lang(
"If you’re having trouble clicking the \":actionText1\" button, copy and paste the URL below\n".
'into your web browser:',
[
'actionText1' => $actionText1,
]
)<span class="break-all">[{{ $dashboardLink }}]({{ $dashboardLink }})</span><p>
@lang(
"If you’re having trouble clicking the \":actionText2\" button, copy and paste the URL below\n".
'into your web browser:',
[
'actionText2' => $actionText2,
]
)<span class="break-all">[{{ $libraryLink }}]({{ $libraryLink }})</span>
@endslot
@endisset
@endcomponent
