@props(['title', 'eyebrow' => 'WORKSPACE', 'description' => ''])
<div class="page-header">
<div>
<p class="eyebrow">{{ $eyebrow }}</p>
<h1>{{ $title }}</h1>@if($description)<p class="muted">{{ $description }}</p>@endif</div>
<div>{{ $slot }}</div>
</div>
