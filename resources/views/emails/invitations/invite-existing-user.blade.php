@component('mail::message')
# こんにちは、

**{{ $invitation->team->name }}**に招待されました。
すでにプラットフォームに登録されているので、
[チーム管理コンソール]({{ $url }})で招待を受け入れるか拒否するだけです。

@component('mail::button', ['url' => $url])
Go to Dashboard
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent
