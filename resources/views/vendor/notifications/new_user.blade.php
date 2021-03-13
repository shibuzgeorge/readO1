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
@isset($verificationEmailLink)
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
@component('mail::button', ['url' => $verificationEmailLink, 'color' => $color])
{{ $actionText1 }}
@endcomponent
@endisset

{{-- Action Button --}}
@isset($resetPasswordLink)
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
@component('mail::button', ['url' => $resetPasswordLink,  'color' => $color])
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
@isset($verificationEmailLink, $resetPasswordLink)
@slot('subcopy')
@lang(
"If you’re having trouble clicking the \":actionText1\" button, copy and paste the URL below\n".
'into your web browser:',
[
'actionText1' => $actionText1,
]
)<span class="break-all">[{{ $verificationEmailLink }}]({{ $verificationEmailLink }})</span><p>
@lang(
"If you’re having trouble clicking the \":actionText2\" button, copy and paste the URL below\n".
'into your web browser:',
[
'actionText2' => $actionText2,
]
)<span class="break-all">[{{ $resetPasswordLink }}]({{ $resetPasswordLink }})</span>
@endslot
@endisset
@endcomponent
