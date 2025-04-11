@component('mail::message')
# Sensible Product Stock Alert

@if($category)
## Category: {{ $category->name }}
@endif

@if($count == 1)
A sensible product is running low on stock and requires your immediate attention.
@else
{{ $count }} products @if($category) in {{ $category->name }} category @endif are running low on stock and require your immediate attention.
@endif

@component('mail::table')
| Product | Code | Current Stock | Min Stock | Status |
| --- | --- | --- | --- | --- |
@foreach($products as $product)
| {{ $product->name }} | {{ $product->code }} | {{ $product->current_stock }} | {{ $product->min_stock }} | {{ $product->current_stock <= 0 ? 'OUT OF STOCK' : 'LOW STOCK' }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => config('app.url').'/products'])
View Products
@endcomponent

Please review these items as soon as possible and take appropriate action.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 