<x-mail::message>
# Order {{ ucfirst($newStatus) }}

{{ $statusMessage }}

**Order:** {{ $orderNumber }}<br>
**Status:** {{ ucfirst($newStatus) }}

**Ship to:**<br>
{{ $order->shipping_name }}<br>
{{ $order->shipping_address }}<br>
{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postcode }}

<x-mail::button :url="$orderUrl">
View Your Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
