<x-layout title="Page not found">
<div class="not-found">
<span class="eyebrow">404 / A SMALL DETOUR</span>
<h1>This page isn’t on the list.</h1>
<p class="muted">The link may have changed, or this account no longer exists.<br>Let’s get you back to familiar ground.</p>
<x-button :href="route('home')">
<x-icon name="back" /> Back to overview</x-button>
</div>
</x-layout>
