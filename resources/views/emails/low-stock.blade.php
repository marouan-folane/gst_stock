@component('mail::message')
# Low Stock Alert Notification

{{ $count }} products are currently below their minimum stock level and require attention.

@component('mail::table')
| Product | Category | Current Stock | Min Stock | Status |
| --- | --- | --- | --- | --- |
@foreach($products as $product)
| {{ $product->name }} | {{ $product->category->name ?? 'N/A' }} | {{ $product->current_stock }} | {{ $product->min_stock }} | {{ $product->current_stock <= 0 ? 'OUT OF STOCK' : 'LOW STOCK' }} |
@endforeach
@endcomponent

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