@component('mail::message')
# Maximum Stock Alert Notification

{{ $count }} products are currently above their maximum stock level and require attention.

@component('mail::table')
| Product | Category | Current Stock | Max Stock | Excess |
| --- | --- | --- | --- | --- |
@foreach($products as $product)
| {{ $product->name }} | {{ $product->category->name ?? 'N/A' }} | {{ $product->current_stock }} | {{ $product->max_stock }} | {{ $product->current_stock - $product->max_stock }} |
@endforeach
@endcomponent

These products are taking up unnecessary storage space and may be at risk of expiring before they can be used.
Consider adjusting inventory or production plans to reduce excess stock.

@component('mail::button', ['url' => config('app.url').'/products'])
View Products
@endcomponent

@component('mail::button', ['url' => config('app.url').'/settings/notifications'])
Notification Settings
@endcomponent

This is an automated message from your inventory management system.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 