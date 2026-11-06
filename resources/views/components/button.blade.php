@props(['href' => null, 'variant' => 'primary'])
@if($href)
<a href="{{ $href }}" {{ $attributes->class(['btn', $variant]) }}>{{ $slot }}</a>
@else
<button {{ $attributes->merge(['type' => 'submit'])->class(['btn', $variant]) }}>{{ $slot }}</button>
@endif
