<x-mail::message>
{!! nl2br(e($refusal)) !!}

**Référence :** {{ $request->reference }}

{{ app(\App\Settings\ShopSettings::class)->business_name }}
</x-mail::message>
