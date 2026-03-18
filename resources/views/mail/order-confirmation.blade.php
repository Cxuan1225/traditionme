<x-mail::message>
# Order Confirmed

Thank you for your order **{{ $orderNumber }}**, placed on {{ $order->created_at->format('d M Y') }}.

<x-mail::table>
| Product | Qty | Unit Price | Subtotal |
|:--------|:---:|:----------:|:--------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | RM {{ number_format($item->unit_price_in_sen / 100, 2) }} | RM {{ number_format($item->subtotal_in_sen / 100, 2) }} |
@endforeach
</x-mail::table>

**Subtotal:** RM {{ number_format($order->subtotal_in_sen / 100, 2) }}
@if ($order->discount_in_sen > 0)
**Discount:** -RM {{ number_format($order->discount_in_sen / 100, 2) }}
@endif
**Shipping:** {{ $order->shipping_in_sen === 0 ? 'Free' : 'RM ' . number_format($order->shipping_in_sen / 100, 2) }}
**Total:** RM {{ number_format($order->total_in_sen / 100, 2) }}

**Ship to:**<br>
{{ $order->shipping_name }}<br>
{{ $order->shipping_address }}<br>
{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postcode }}<br>
{{ $order->shipping_phone }}

<x-mail::button :url="$orderUrl">
View Your Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
