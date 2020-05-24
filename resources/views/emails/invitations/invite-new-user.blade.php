@component('mail::message')
# こんにちは、

**{{ $invitation->team->name }}**に招待されました。
まだプラットフォームにサインアップしていないため、
[無料で登録]({{ $url }})してください。その後、チーム管理コンソールで招待を承諾または拒否できます。

@component('mail::button', ['url' => $url])
無料で登録
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent
