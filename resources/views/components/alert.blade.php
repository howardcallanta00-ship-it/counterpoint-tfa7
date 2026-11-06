@props(['variant' => 'success'])
<div {{ $attributes->class(['alert', $variant]) }} role="alert">
<x-icon name="check" />
<div>{{ $slot }}</div>
</div>
